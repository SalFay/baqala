<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class InvoiceRepository.
 * @package namespace App\Entities;
 */
class InvoiceRepository extends Model implements Transformable
{
  use TransformableTrait;
  
  /**
   * The attributes that are mass assignable.
   * @var array
   */
  protected $fillable = [];
  
}
