<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
  
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function store( Request $request )
  {
    $this->validate( $request, [
      'name' => 'required|string|max:191|unique:categories'
    ], [
      'name.required' => 'Category Name is Required',
    ] );
    $data = $request->all();
    if( empty( $request->code ) ) {
      $data[ 'code' ] = $request->name;
    }
    Category::create( $data );
    return response()->json( [ 'status' => 'ok', 'message' => 'Category Added' ], 200 );
  } // store
  
  /**
   * @param Request $request
   * @param category $category
   * @return object
   * @throws ValidationException
   */
  public function update( Request $request, Category $category ) : object
  {
    $this->validate( $request, [
      'name' => [
        'required', 'string', 'max:191',
        Rule::unique( 'categories' )->ignore( $category->id ),
      ],
    ], [
      'name.required' => 'Category Name is Required',
    ] );
    //update the Category
    
    $category->update( $request->all() );
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Category Updated' ], 200 );
  } // update
  
  /**
   * @param category $category
   * @return array|string[]
   * @throws \Exception
   */
  public function destroy( Category $category ) : array
  {
    $category->delete();
    return [ 'status' => 'ok', 'message' => 'Category Deleted' ];
  }
}
