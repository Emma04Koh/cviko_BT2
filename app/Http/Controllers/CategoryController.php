<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$categories = DB::table('categories')->get();
        //return response()->json($categories);

        return response()->json(Category::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Pri API sa zvyčajne nepoužíva
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /*$id = DB::table('categories')->insertGetId([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Vytvorené'], 201);*/

        $category = Category::create([
            'name' => $request->name,
            'color' => $request->color ?? '#fffffe',
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        /*$category = DB::table('categories')->where('id', $id)->first();
        if (!$category) {
            return response()->json(['message' => 'Nenájdené'], 404);
        }
        return response()->json($category);*/

        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Nenájdené'], 404);
        return response()->json($category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Pri API sa zvyčajne nepoužíva
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        /*DB::table('categories')->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Upravené']);*/

        $category = Category::findOrFail($id);
        $category->update($request->only(['name', 'color']));
        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /*DB::table('categories')->where('id', $id)->delete();
        return response()->json(['message' => 'Zmazané']);*/

        Category::destroy($id);
        return response()->json(['message' => 'Zmazané']);
    }
}
