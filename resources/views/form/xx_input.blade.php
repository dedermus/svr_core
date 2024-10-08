@include("admin::form._header")

<div class="input-group">
    @if ($prepend)
        <span class="input-group-text with-icon">{!! $prepend !!}</span>
    @endif

    <input {!! $attributes !!} />

    @if ($append)
        <span class="input-group-text clearfix">{!! $append !!}</span>
    @endif

    @isset($btn)
        <span class="input-group-btn">
                  {!! $btn !!}
                </span>
    @endisset

    @isset($invalid_feedback)
        @include("svr-core::form.xx_invalid-feedback-block")
    @endisset

</div>

@include("admin::form._footer")

