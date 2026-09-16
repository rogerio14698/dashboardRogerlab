<form id="{{ $formId }}" method="POST" action="{{ $editing ? route('database.update') : route('database.store') }}" class="record-form">
    @csrf
    @if ($editing) @method('PATCH') @endif
    <input type="hidden" name="database" value="{{ $database }}">
    <input type="hidden" name="table" value="{{ $table }}">
    @if ($editing && $primaryKey)
        <input type="hidden" name="primary_value" value="{{ $row[$primaryKey] ?? '' }}">
        <h3>Editar registro</h3>
    @else
        <h3>Nuevo registro</h3>
    @endif
    <div class="record-form__fields">
        @foreach ($columns as $column)
            @continue(str_contains(strtolower($column['extra']), 'auto_increment'))
            @continue($editing && $column['name'] === $primaryKey)
            @php
                $rawValue = $row[$column['name']] ?? $column['default'] ?? '';
                $value = is_object($rawValue) || is_array($rawValue) ? json_encode($rawValue) : $rawValue;
                $inputType = preg_match('/int|decimal|float|double/i', $column['type']) ? 'number' : (preg_match('/date|time/i', $column['type']) ? 'datetime-local' : 'text');
            @endphp
            <label class="field">{{ $column['name'] }} <small class="muted">{{ $column['type'] }}</small>
                @if (preg_match('/tinyint\(1\)|boolean/i', $column['type']))
                    <select name="values[{{ $column['name'] }}]">
                        <option value="">Sin valor</option><option value="1" @selected((string) old('values.' . $column['name'], $value) === '1')>Si</option><option value="0" @selected((string) old('values.' . $column['name'], $value) === '0')>No</option>
                    </select>
                @elseif (preg_match('/text|json/i', $column['type']))
                    <textarea name="values[{{ $column['name'] }}]" rows="3">{{ old('values.' . $column['name'], $value) }}</textarea>
                @else
                    <input name="values[{{ $column['name'] }}]" type="{{ $inputType }}" value="{{ old('values.' . $column['name'], $value) }}">
                @endif
            </label>
        @endforeach
    </div>
    <div style="margin-top: 1rem"><button class="button button--primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Crear registro' }}</button></div>
</form>
