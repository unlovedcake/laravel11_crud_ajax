<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {

            $data = Product::query();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($row) {


                    $url = asset($row->image);
                    return '<img src="' . $url . '" width="50" height="50"/>';
                })

                ->addColumn('action', function ($row) {

                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $row->id . '" data-original-title="View" class="me-1 btn btn-info btn-sm showProduct"><i class="fa-regular fa-eye"></i> View</a>';
                    $btn = $btn . '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $row->id . '" data-original-title="Edit" class="edit btn btn-primary btn-sm editProduct"><i class="fa-regular fa-pen-to-square"></i> Edit</a>';

                    $btn = $btn . ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $row->id . '" data-original-title="Delete" class="btn btn-danger btn-sm deleteProduct"><i class="fa-solid fa-trash"></i> Delete</a>';

                    return $btn;
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('product.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        // $request->validate([
        //     'name' => 'required',
        //     'price' => 'required',
        // ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $filename = NULL;
        $path = NULL;

        if ($request->has('image')) {

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();

            $filename = time() . '.' . $extension;

            $path = 'uploads/product/';
            $file->move($path, $filename);
        }


        $product = Product::updateOrCreate(
            [
                'id' => $request->product_id
            ],
            [
                'name' => $request->name,
                'price' => $request->price,
                'image' => $path . $filename,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Product added successfully',
            'product' => $product
        ], 201);

        // return response()->json(['success' => 'Product saved successfully.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $product = Product::find($id);

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): JsonResponse
    {
        $product = Product::find($id);
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        Product::find($id)->delete();

        return response()->json(['success' => 'Product deleted successfully.']);
    }
}
