<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\{Column, Footer, Header, PowerGrid, PowerGridColumns, PowerGridComponent};
use PowerComponents\LivewirePowerGrid\Filters\Filter;
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;

final class UserTable extends PowerGridComponent
{
  use ActionButton;
  
  /*
  |--------------------------------------------------------------------------
  |  Features Setup
  |--------------------------------------------------------------------------
  | Setup Table's general features
  |
  */
  public function setUp() : array
  {
    
    return [
      Header::make()->showSearchInput(),
      Footer::make()
            ->showPerPage()->showRecordCount( mode: 'short' )
    ];
  }
  
  /*
  |--------------------------------------------------------------------------
  |  Datasource
  |--------------------------------------------------------------------------
  | Provides data to your Table using a Model or Collection
  |
  */
  
  /**
   * @return Builder
   */
  public function datasource() : Builder
  {
    return User::query()->orderBy( 'id', 'DESC' );
  }
  
  /*
  |--------------------------------------------------------------------------
  |  Relationship Search
  |--------------------------------------------------------------------------
  | Configure here relationships to be used by the Search and Table Filters.
  |
  */
  
  /**
   * Relationship search.
   * @return array<string, array<int, string>>
   */
  public function relationSearch() : array
  {
    return [];
  }
  
  /*
  |--------------------------------------------------------------------------
  |  Add Column
  |--------------------------------------------------------------------------
  | Make Datasource fields available to be used as columns.
  | You can pass a closure to transform/modify the data.
  |
  | ❗ IMPORTANT: When using closures, you must escape any value coming from
  |    the database using the `e()` Laravel Helper function.
  |
  */
  public function addColumns() : PowerGridColumns
  {
    return PowerGrid::columns()
                    ->addColumn( 'id' )
                    ->addColumn( 'action', function( User $model ) {
                      return true; //view( 'admin.products.actions', [ 'id' => $model->id ] );
                    } )
                    ->addColumn( 'name' )
                    ->addColumn( 'email' )
                    ->addColumn( 'name_lower', fn( User $model ) => strtolower( e( $model->name ) ) )
                    ->addColumn( 'created_at' )
                    ->addColumn( 'created_at_formatted',
                      fn( User $model ) => Carbon::parse( $model->created_at )->format( 'd/m/Y H:i:s' ) );
  }
  
  /*
  |--------------------------------------------------------------------------
  |  Include Columns
  |--------------------------------------------------------------------------
  | Include the columns added columns, making them visible on the Table.
  | Each column can be configured with properties, filters, actions...
  |
  */
  
  /**
   * PowerGrid Columns.
   * @return array<int, Column>
   */
  public function columns() : array
  {
    return [
      Column::make( 'ID', 'id' )
            ->searchable()
            ->sortable(),
      Column::make( 'ACTION', 'action' ),
      
      Column::make( 'Name', 'name' )
            ->searchable()
            ->sortable(),
      
      Column::make( 'Email', 'email' )
            ->searchable()
            ->sortable(),
      
      Column::make( 'Created at', 'created_at' )
            ->hidden(),
      
      Column::make( 'Created at', 'created_at_formatted', 'created_at' )
            ->searchable()
    ];
  }
  
  /**
   * PowerGrid Filters.
   * @return array<int, Filter>
   */
  public function filters() : array
  {
    return [
      Filter::datepicker( 'created_at_formatted', 'created_at' ),
    ];
  }
  
  /*
  |--------------------------------------------------------------------------
  | Actions Method
  |--------------------------------------------------------------------------
  | Enable the method below only if the Routes below are defined in your app.
  |
  */
  
}
