<?php

namespace App\Http\Controllers\Api;

use App\Models\Categorie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategorieResource;
use Illuminate\Support\Facades\Validator;

class CategorieController extends Controller
{
    public function index()
    {
        
        $categorie= Categorie::get();
        if ($categorie->count() > 0) {
            return CategorieResource::collection($categorie);
        }else{

            return response()->json(['message'=>'No Categorie Available'],200);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator =Validator::make($request->all(),[
            'name'=>'required|string|max:255|unique:categories',
            'category_id'=>'integer|exists:categories,id',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'=>$validator->messages(),
            ],422);
        }
        

        $data = Categorie::create([
            'name'=>$request->name,
            'category_id'=>$request->category_id,

        ]);

        return response()->json([
            'message'=>'Categorie created successfully',
            'DATA' => new CategorieResource($data) ,
        ],200);


    }

    /**
     * Display the specified resource.
     */
    public function show(Categorie $Category)
    {
        return new CategorieResource($Category) ;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categorie $Category)
    {
        $validator =Validator::make($request->all(),[
           'name'=>'required|string|max:255|unique:categories',
           'category_id'=>'required|integer|exists:categories,id',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'=>$validator->messages(),
            ],422);
        } 
        

        $Category->update([
            'name'=>$request->name,
            'category_id'=>$request->category_id,
        ]);

        return response()->json([
            'message'=>'Categorie updated successfully',
            'DATA' => new CategorieResource($Category) ,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorie $Category)
    {
        $Category->delete();
        return response()->json([
            'message'=>'Categorie deleted successfully',
        ],200);
    }
}
