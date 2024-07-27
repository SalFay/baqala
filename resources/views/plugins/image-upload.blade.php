<div class="image-upload" data-media="images" data-input="{{$id}}" data-preview="{{$id}}-preview">
    <p class="text-center"><strong>Click to choose an Image</strong></p>
</div>
<div class="form-group image-upload-preview">
    <img src="{{old($name,$value??'')}}" id="{{$id}}-preview" alt="Image" class="col-sm-2 img-thumbnail">
    <input type="hidden" name="{{$name}}" id="{{$id}}" value="{{old($name,$value??'')}}" class="form-control">
    <div class="clearfix"></div>
</div>
@push('head')
    <style>
        .image-upload-preview img[src=""]{
            display: none;
        }
    </style>
@endpush
