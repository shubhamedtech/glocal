<?php 
  if(isset($_GET['id'])){
    require '../../includes/db-config.php';

    $id = intval($_GET['id']);
    $user = $conn->query("SELECT Name, Email, Mobile, Photo FROM Users WHERE ID = $id");
    $user = mysqli_fetch_assoc($user);
  }
?>
<!-- Modal -->
<div class="modal-header clearfix text-left">
  <h5>Edit <span class="semi-bold"></span>Sub-Center</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<form role="form" id="form-edit-sub-centers" action="/app/sub-centers/update" method="POST" enctype="multipart/form-data">
  <div class="modal-body">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Name</label>
          <input type="text" name="name" class="form-control" placeholder="ex: Jhon Doe" value="<?=$user['Name']?>" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Email</label>
          <input type="text" name="email" class="form-control" value="<?=$user['Email']?>" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Mobile</label>
          <input type="tel" name="mobile" class="form-control" value="<?=$user['Mobile']?>" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group form-group-default required">
          <label>Photo</label>
          <input type="file" name="image" class="form-control" accept="image/png, image/jpg, image/jpeg, image/svg">
          <img id="image" src="<?=$user['Photo']?>" alt="User Photo" />
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer clearfix text-end">
    <div class="col-md-4 m-t-10 sm-m-t-10">
      <button aria-label="" type="submit" class="btn btn-primary btn-cons btn-animated from-left">
        <i class="ti-save-alt mr-2"></i>
        <span>Update</span>
      </button>
    </div>
  </div>
</form>

<script>
  $(function(){
    $('#form-edit-sub-centers').validate({
      rules: {
        name: {required:true},
      },
      highlight: function (element) {
        $(element).addClass('error');
        $(element).closest('.form-control').addClass('has-error');
      },
      unhighlight: function (element) {
        $(element).removeClass('error');
        $(element).closest('.form-control').removeClass('has-error');
      }
    });
  })

  $("#form-edit-sub-centers").on("submit", function(e){
    if($('#form-edit-sub-centers').valid()){
      $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      formData.append('id', '<?=$id?>');
      $.ajax({
          url: this.action,
          type: 'post',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(data) {
            if(data.status==200){
              $('.modal').modal('hide');
              notification('success', data.message);
              $('#users-table').DataTable().ajax.reload(null, false);
            }else{
              $(':input[type="submit"]').prop('disabled', false);
              notification('danger', data.message);
            }
          }
      });
      e.preventDefault();
    }
  });
</script>
