<style>
  #camera_wrapper, #show_saved_img{}
</style>
<div Class="row justify-content-center">
  <div class="col-md-4 col-12">
    <div class="card border rounded shadow text-center">
      <div class="card-body">
      	<video id="video" style="width:100%; height:auto;" autoplay></video><br>
		    <button class="btn btn-primary" id="start-camera">Start Camera</button>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-12">
    <div class="card border rounded shadow text-center">
      <div class="card-body">
      	<canvas id="canvas" style="width:100%; height:300px;"></canvas><br>
		    <button class="btn btn-primary" id="click-photo">Capture Photo</button>
      </div>
    </div>
  </div>
</div>


<form method="POST" class="text-center" action="/app/exams/uploadwecampic">
 	<input id="photo" name="photo" class="d-none"/>
	<button class="btn btn-success" type="button" id="submit_form" onclick="subExam();">Submit and Start Exam</button>
</form>

<script async>
     $("#submit_form").hide();
     $("#click-photo").hide();
    let camera_button = document.querySelector("#start-camera");
    let video = document.querySelector("#video");
    let click_button = document.querySelector("#click-photo");
    let canvas = document.querySelector("#canvas");

    camera_button.addEventListener('click', async function() {
        $("#click-photo").show();
        let stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        video.srcObject = stream;
    });

    click_button.addEventListener('click', function() {
      	$("#submit_form").show();
      	$("#results").html('');
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        let image_data_url = canvas.toDataURL('image/jpeg');
      	$("#photo").val(image_data_url);
    });
    
    function subExam(){
        startExam();
    }
</script>
