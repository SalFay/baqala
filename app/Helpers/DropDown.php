<?php

namespace App\Helpers;

use App\Models\Area;
use App\Models\Nationality;
use App\Models\Taxonomy;
use Illuminate\Support\Collection;
use SalFay\JobPortal\World\Models\Country;

class DropDown
{
  public static function team() : array
  {
    return [
      '0'   => 'No',
      '5'   => '0 - 5 members',
      '10'  => '5 - 10 members',
      '15'  => '10 - 15 members',
      '20'  => '15 - 20 members',
      '25'  => '20 - 25 members',
      '30'  => '25 - 30 members',
      '35'  => '30 - 35 members',
      '35+' => '35+ members',
    ];
  }
  
  /**
   * @param bool $select2
   * @return array|Collection
   */
  public static function countries( $select2 = false )
  {
    $query = Country::select( [ 'id', 'name' ] )->get();
    
    $countries = [];
    
    if( $select2 !== false ) {
      return $query;
    }
    
    foreach( $query as $item ) {
      $countries[ $item->id ] = $item->name;
    }
    
    return $countries;
  }
  
  /**
   * @param array $prepend
   * @return array
   */
  public static function gender( $prepend = [ '' => 'Select' ] ) : array
  {
    $output = $prepend;
    
    $taxonomies = Taxonomy::whereType( 'gender' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }// gender
  
  /**
   * @return array
   */
  public static function maritalStatus() : array
  {
    $output = [];
    
    $taxonomies = Taxonomy::whereType( 'marital-status' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }
  
  /**
   * @return array
   */
  public static function nationalities() : array
  {
    $all = Nationality::all();
    
    $output = [];
    
    foreach( $all as $item ) {
      $output[ $item->id ] = $item->name;
    }
    
    return $output;
  }
  
  public static function areas() : array
  {
    $all = Area::all();
    
    $output = [];
    
    foreach( $all as $item ) {
      $output[ $item->id ] = $item->name;
    }
    
    return $output;
  }
  
  public static function cities( $countryId = '' ) : array
  {
    $country = Country::byCode( 'pk' );
    $cities = [];
    foreach( $country->cities as $city ) {
      $cities[ $city->id ] = $city->name;
    }
    
    return $cities;
  }
  
  /**
   * @return array
   */
  public static function careerLevels() : array
  {
    $output = [];
    
    $taxonomies = Taxonomy::whereType( 'career-level' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }
  
  /**
   * @param array $prepend
   * @return array
   */
  public static function experience( $prepend = [ '' => 'Select' ] ) : array
  {
    $output = $prepend;
    
    $taxonomies = Taxonomy::whereType( 'experience' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }// experience
  
  /**
   * @return array
   */
  public static function salary() : array
  {
    $output = [];
    
    $taxonomies = Taxonomy::whereType( 'salary' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }// salary
  
  /**
   * @return array
   */
  public static function industry() : array
  {
    $output = [];
    
    $taxonomies = Taxonomy::whereType( 'industry' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }// industry
  
  /**
   * @return array
   */
  public static function studyFields() : array
  {
    $output = [];
    
    $taxonomies = Taxonomy::whereType( 'study-field' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }// studyFields
  
  /**
   * @return array
   */
  public static function degree() : array
  {
    $output = [];
    
    $taxonomies = Taxonomy::whereType( 'degree' )->get();
    
    foreach( $taxonomies as $item ) {
      $output[ $item->id ] = $item->label;
    }
    
    return $output;
  }// degree
  
  /**
   * @param array $prepend
   * @return array
   */
  public static function ownershipTypes( $prepend = [ '' => 'Select' ] ) : array
  {
    return $prepend + [
        'Sole Proprietorship' => 'Sole Proprietorship',
        'Public'              => 'Public',
        'Private'             => 'Private',
        'Government'          => 'Government',
        'NGO'                 => 'NGO'
      ];
  }
  
  /**
   * @param array $prepend
   * @return array
   */
  public static function employees( $prepend = [ '' => 'Select' ] ) : array
  {
    return $prepend + [
        '1-10'           => '1-10',
        '11-50'          => '11-50',
        '51-100'         => '51-100',
        '101-200'        => '101-200',
        '201-300'        => '201-300',
        '301-600'        => '301-600',
        '601-1000'       => '601-1000',
        '1001-1500'      => '1001-1500',
        '1501-2000'      => '1501-2000',
        '2001-2500'      => '2001-2500',
        '2501-3000'      => '2501-3000',
        '3001-3500'      => '3001-3500',
        '3501-4000'      => '3501-4000',
        '4001-4500'      => '4001-4500',
        '4501-5000'      => '4501-5000',
        'More than 5000' => 'More than 5000',
      ];
  }
  
  /**
   * @param array $prepend
   * @return array
   */
  public static function jobType( $prepend = [ '' => 'Select' ] ) : array
  {
    return $prepend + [
        'Internship'          => 'Internship',
        'Freelance'           => 'Freelance',
        'Contract'            => 'Contract',
        'Part Time'           => 'Part Time',
        'Full Time/Permanent' => 'Full Time/Permanent'
      ];
  }// jobType
  
  /**
   * @param array $prepend
   * @return array
   */
  public static function jobShiftType( $prepend = [ '' => 'Select' ] ) : array
  {
    return $prepend + [
        'First Shift (Day)'        => 'First Shift (Day)',
        'Second Shift (Afternoon)' => 'Second Shift (Afternoon)',
        'Third Shift (Night)'      => 'Third Shift (Night)',
        'Rotating'                 => 'Rotating',
        'Multiple'                 => 'Multiple'
      ];
  }
  
  /**
   * @return array
   */
  public static function languageProficiency() : array
  {
    return [
      'Beginner'     => 'Beginner',
      'Intermediate' => 'Intermediate',
      'Expert'       => 'Expert'
    ];
  }
  
  public static function routes()
  {
    return [
      'home'                 => 'Homepage',
      'profile'              => 'Job Seeker Profile',
      'cv'                   => 'C.V Manager',
      'cv.profile'           => 'Profile C.V',
      'customersJobs.search' => 'Search Jobs',
      'user.dashboard'       => 'Job Seeker Dashboard',
      'companies'            => 'Companies Manager',
      'company.add'          => 'Add Company',
      'employer.dashboard'   => 'Employer Dashboard',
      'customersJobs.manage' => 'Jobs Manager',
      'job.post'             => 'Post Job',
      'team.manage'          => 'Manage Team',
      'team.add'             => 'Add Team Member',
      'messenger'            => 'Messenger',
      'job-alerts'           => 'Job Alerts',
      'notification'         => 'Notifications',
    ];
  }
  
  /**
   * @return array
   */
  public static function functionalAreas() : array
  {
    $model = Taxonomy::query();
    
    $model->where( 'type', 'functional-area' );
    $model = $model->get();
    
    $dropdown = [];
    
    foreach( $model as $item ) {
      $dropdown[ $item->id ] = $item->label;
    }
    
    return $dropdown;
    
  }// functionalAreas
  
}
