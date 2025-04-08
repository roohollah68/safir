<table class="table stripe">
    <thead>
    <tr>
        <th>شماره</th>
        <th>نام</th>
        <th>مقدار</th>
        <th>واحد</th>
        <th>عملیات</th>
    </tr>
    </thead>
    <tbody>
    @foreach($good->formulations as $formulation)
        <tr>
            <td>{{$formulation->rawGood_id}}</td>
            <td>{{$formulation->rawGood->name}}</td>
            <td>
                <input type="number" value="{{$formulation->amount}}" id="formulation-{{$formulation->id}}" min="0"
                       step="0.0001">
            </td>
            <td>
                <select id="unit-{{$formulation->id}}" class="form-control" name="unit_id">
                    @foreach(\App\Models\Unit::all() as $unit)
                        <option value="{{$unit->id}}" @selected(($formulation->rawGood->unit_id??1 )== $unit->id)>
                            {{$unit->name}}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <span class="btn btn-success fa fa-save" onclick="editFormulation({{$formulation->id}})"></span>
                <span class="btn btn-danger fa fa-trash" onclick="deleteFormulation({{$formulation->id}})"></span>
            </td>
        </tr>
    </tbody>
    @endforeach
</table>

