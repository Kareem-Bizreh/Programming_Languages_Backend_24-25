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
     *     path="/categories",
     *     summary="get all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *      response=200, description="Successful get categories",@OA\JsonContent()),
     * )
     */
    public function getCategories()
    {
        return response()->json(['categories: ' => $this->categoryRepositry->getAll()], 200);
    }
}
