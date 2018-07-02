<div class="form-group row">
    <label class="col-md-4 col-form-label text-md-right" for="{{ $name }}">
        {{ $label }}
    </label>
    <div class="col-md-6">
        <select name="{{ $name }}" class="form-control {{ $class }}">
            @foreach($options as $key => $option)
                <option value="{{ $key }}">{{ $option }}</option>
            @endforeach
        </select>
    </div>
</div>