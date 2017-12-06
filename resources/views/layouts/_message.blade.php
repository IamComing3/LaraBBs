@if (Session::has('message'))
    <div class="alert alert-info">
        <button type="button" name="button" class="colse" data-dismiss="alert" aria-hidden="true">×</button>
        {{ Session::get('message') }}
    </div>
@endif

@if (Session::has('success'))
    <div class="alert alert-success">
        <button type="button" name="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ Session::get('success') }}
    </div>
@endif

@if (Session::has('danger'))
    <div class="alert alert-success">
        <button type="button" name="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ Session::get('danger') }}
    </div>
@endif