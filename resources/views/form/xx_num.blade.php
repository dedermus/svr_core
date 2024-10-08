
@include("admin::form._header")

<div class="input-group">
    <button type="button" id="{{$id}}-button-min" class="input-group-text btn btn-light minus with-icon"><i class="icon-minus"></i></button>
    <input {!! $attributes !!} />
    <button type="button" id="{{$id}}-button-plus" class="input-group-text btn btn-light plus with-icon"><i class="icon-plus"></i></button>
    @isset($invalid_feedback)
        {{--        @include("admin::form.xx_invalid-feedback-block")--}}
        <div class="invalid-feedback">{!! $invalid_feedback !!}</div>
        {{-- Ответ на невалидность --}}
    @endisset
</div>

@include("admin::form._footer")
