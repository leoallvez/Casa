@if($errors->any())
  @foreach($errors->all() as $error)
    <div class="alert alert-success alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      	<span aria-hidden="true">×</span>
      </button>
      <strong>{{ $error }}</strong>
    </div>
  @endforeach
@endif
