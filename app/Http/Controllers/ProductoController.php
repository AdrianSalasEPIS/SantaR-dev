<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Bitacora;
use App\Models\Categoria;
use App\Models\Producto;
use App\Support\ImagenProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

/**
 * CU-07 Gestionar productos e implementos (RF-08). La imagen se sube junto con el producto.
 * El stock no se edita aquí: cambia únicamente con movimientos de inventario (RF-14, RN-09).
 */
class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'categoria_id' => ['nullable', 'integer'],
            'tipo' => ['nullable', 'in:'.implode(',', array_keys(Producto::TIPOS))],
            'estado' => ['nullable', 'in:activos,inactivos'],
        ]);

        $productos = Producto::with('categoria')
            ->when($filtros['buscar'] ?? null, fn ($q, $v) => $q->where('nombre', 'like', '%'.addcslashes($v, '%_\\').'%'))
            ->when($filtros['categoria_id'] ?? null, fn ($q, $v) => $q->where('categoria_id', $v))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('tipo', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('activo', $v === 'activos'))
            ->orderByDesc('activo')->orderBy('nombre')
            ->paginate(12)->withQueryString();

        return view('productos.index', [
            'productos' => $productos,
            'categorias' => Categoria::orderBy('orden_carta')->orderBy('nombre')->get(),
            'filtros' => $filtros,
        ]);
    }

    public function create(): View
    {
        return view('productos.form', [
            'producto' => new Producto(['tipo' => 'PRODUCTO', 'visible_en_carta' => true, 'stock_minimo' => 5]),
            'categorias' => Categoria::activas()->get(),
        ]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $datos = $request->safe()->except(['imagen']);
        $imagen = $request->hasFile('imagen') ? ImagenProducto::guardar($request->file('imagen')) : null;

        try {
            $producto = DB::transaction(function () use ($datos, $imagen) {
                $producto = Producto::create($datos + ['imagen' => $imagen, 'activo' => true]);
                Bitacora::registrar('catalogo', 'producto creado', $producto->nombre.($producto->esImplemento() ? ' · implemento' : " · S/ {$producto->precio}"));

                return $producto;
            });
        } catch (Throwable $e) {
            ImagenProducto::eliminar($imagen); // no dejar archivos huérfanos si falla el registro
            throw $e;
        }

        return redirect()->route('productos.index')->with('exito', "«{$producto->nombre}» registrado correctamente.");
    }

    public function edit(Producto $producto): View
    {
        return view('productos.form', [
            'producto' => $producto,
            'categorias' => Categoria::where('activo', true)->orWhere('id', $producto->categoria_id)
                ->orderBy('orden_carta')->orderBy('nombre')->get(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $datos = $request->safe()->except(['imagen']);
        $precioAnterior = $producto->precio;
        $imagenAnterior = $producto->imagen;

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = ImagenProducto::guardar($request->file('imagen'));
        }

        $producto->update($datos);

        if (isset($datos['imagen'])) {
            ImagenProducto::eliminar($imagenAnterior);
        }

        $detalle = $producto->nombre;
        if ((string) $precioAnterior !== (string) $producto->precio) {
            $detalle .= " · precio S/ {$precioAnterior} → S/ {$producto->precio}";
        }
        Bitacora::registrar('catalogo', 'producto editado', $detalle);

        return redirect()->route('productos.index')->with('exito', "«{$producto->nombre}» actualizado. Los cambios ya se reflejan en la carta.");
    }

    public function cambiarEstado(Producto $producto): RedirectResponse
    {
        if (! $producto->activo && ! $producto->categoria->activo) {
            return back()->with('error', "No se puede reactivar «{$producto->nombre}» porque su categoría «{$producto->categoria->nombre}» está desactivada.");
        }

        $producto->update(['activo' => ! $producto->activo]);
        Bitacora::registrar('catalogo', $producto->activo ? 'producto reactivado' : 'producto desactivado', $producto->nombre);

        return back()->with('exito', "«{$producto->nombre}» ".($producto->activo ? 'reactivado.' : 'desactivado; su historial se conserva.'));
    }
}
