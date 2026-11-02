<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** CU-06 Gestionar categorías (RF-07, RN-18). */
class CategoriaController extends Controller
{
    public function index(): View
    {
        return view('categorias.index', [
            'categorias' => Categoria::withCount(['productos as productos_activos' => fn ($q) => $q->where('activo', true)])
                ->orderByDesc('activo')->orderBy('orden_carta')->orderBy('nombre')->get(),
        ]);
    }

    public function create(): View
    {
        $siguiente = (int) Categoria::max('orden_carta') + 1;

        return view('categorias.form', ['categoria' => new Categoria(['orden_carta' => $siguiente, 'activo' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categoria = Categoria::create($this->validar($request) + ['activo' => true]);
        Bitacora::registrar('catalogo', 'categoría creada', $categoria->nombre);

        return redirect()->route('categorias.index')->with('exito', "Categoría «{$categoria->nombre}» creada.");
    }

    public function edit(Categoria $categoria): View
    {
        return view('categorias.form', ['categoria' => $categoria]);
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($this->validar($request, $categoria));
        Bitacora::registrar('catalogo', 'categoría editada', $categoria->nombre);

        return redirect()->route('categorias.index')->with('exito', "Categoría «{$categoria->nombre}» actualizada.");
    }

    public function cambiarEstado(Categoria $categoria): RedirectResponse
    {
        if ($categoria->activo && $categoria->productos()->where('activo', true)->exists()) {
            return back()->with('error', "No se puede desactivar «{$categoria->nombre}» porque tiene productos activos (RN-18).");
        }

        $categoria->update(['activo' => ! $categoria->activo]);
        Bitacora::registrar('catalogo', $categoria->activo ? 'categoría reactivada' : 'categoría desactivada', $categoria->nombre);

        return back()->with('exito', "Categoría «{$categoria->nombre}» ".($categoria->activo ? 'reactivada.' : 'desactivada.'));
    }

    private function validar(Request $request, ?Categoria $categoria = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('categorias', 'nombre')->ignore($categoria)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'orden_carta' => ['required', 'integer', 'min:1', 'max:999'],
        ], [], ['orden_carta' => 'orden en la carta']);
    }
}
