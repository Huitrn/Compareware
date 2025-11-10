<?php

namespace App\Http\Controllers;

use App\Models\Periferico;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    /**
     * Mostrar imagen desde base de datos (BLOB)
     */
    public function show($id)
    {
        $periferico = Periferico::findOrFail($id);
        
        if (!$periferico->imagen_blob) {
            abort(404, 'Imagen no encontrada');
        }
        
        $mimeType = $periferico->imagen_mime_type ?? 'image/jpeg';
        
        return response($periferico->imagen_blob)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000'); // Cache por 1 año
    }
    
    /**
     * Subir imagen manualmente
     */
    public function upload(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
        ]);
        
        $periferico = Periferico::findOrFail($id);
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Guardar como archivo
            $path = $file->store('images/perifericos', 'public');
            
            $periferico->update([
                'imagen_path' => $path,
                'imagen_mime_type' => $file->getMimeType(),
                'imagen_source' => 'manual'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen subida correctamente',
                'url' => asset('storage/' . $path)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No se recibió ninguna imagen'
        ], 400);
    }
}

