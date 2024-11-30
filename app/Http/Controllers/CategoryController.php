<?php

namespace App\Http\Controllers;

use App\Repositories\CategoryRepositry;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryRepositry;

    public function __construct(CategoryRepositry $categoryRepositry)
    {
        $this->categoryRepositry = $categoryRepositry;
    }

    /**
     * @OA\Get(
     *     path="/categories/getAll",
     *     summary="get all categories",
     *     tags={"Categories"},
     *     @OA\Response(response=200, description="succesful get all categories",@OA\JsonContent()),
     *     @OA\Response(response=400, description="Invalid request"),
     * )
     */
    public function getAll()
    {
        return response()->json(['categories' => $this->categoryRepositry->getAll()], 200);
    }
}