@if(isset($sf['imagesArray']) && is_array(json_decode($sf['imagesArray'], true)))
    <div class="images-slider">
        @foreach(json_decode($sf['imagesArray'], true) as $item)
            <div><img src="{{$item['image']}}" alt=""></div>
        @endforeach
    </div>
@endif