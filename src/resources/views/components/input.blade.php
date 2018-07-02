<div class="form-group row">
    <label class="col-md-4 col-form-label text-md-right" for="{{ $name }}">
        {{ $label }}
    </label>
    <div class="col-md-6">
        <input name="{{ $name }}" type="{{ $type }}" value="{{ $value ?? '' }}" class="form-control {{ $class ?? '' }}"/>
    </div>
</div>