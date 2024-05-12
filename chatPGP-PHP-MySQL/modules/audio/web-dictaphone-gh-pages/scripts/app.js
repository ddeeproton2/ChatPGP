// set up basic variables for app

const record = document.querySelector('.record');
const stop = document.querySelector('.stop');
const send = document.querySelector('.send');
const soundClips = document.querySelector('.sound-clips');
const canvas = document.querySelector('.visualizer');
const mainSection = document.querySelector('.main-controls');

// disable stop button while not recording

stop.disabled = true;

// visualiser setup - create web audio api context and canvas

let audioCtx;
const canvasCtx = canvas.getContext("2d");
var id_anim = undefined;
//main block for doing the audio recording

if (navigator.mediaDevices.getUserMedia) {
    console.log('getUserMedia supported.');

    const constraints = { audio: true };
    let chunks = [];
    var blob = undefined;
    let onSuccess = function(stream) {
        const mediaRecorder = new MediaRecorder(stream);
        var duration =  undefined;
        var handleDuration = undefined;
        var timerDuration = function(){
            var total_secondes = (Date.now()-duration) / 1000;  
            let nb_jours = Math.floor(total_secondes / (60 * 60 * 24));
            var nb_heures = Math.floor((total_secondes - (nb_jours * 60 * 60 * 24)) / (60 * 60));
            var nb_minutes = Math.floor((total_secondes - ((nb_jours * 60 * 60 * 24 + nb_heures * 60 * 60))) / 60);
            var nb_secondes = Math.floor(total_secondes - ((nb_jours * 60 * 60 * 24 + nb_heures * 60 * 60 + nb_minutes * 60)));
            var dom_duration = document.querySelector('.duration');
            dom_duration.innerHTML = addZero(nb_minutes)+":"+addZero(nb_secondes);
        };

        record.onclick = function() {
            visualize(stream);
            chunks = [];
            chunks_to_send = [];
            mediaRecorder.start();
            console.log(mediaRecorder.state);
            console.log("recorder started");
            record.style.background = "red";
            stop.disabled = false;
            record.disabled = true;
            duration =  Date.now();
            handleDuration = setInterval(timerDuration, 1000);
        };

        stop.onclick = function() {
            mediaRecorder.stop();
            console.log(mediaRecorder.state);
            console.log("recorder stopped");
            record.style.background = "";
            record.style.color = "";
            // mediaRecorder.requestData();
            stop.disabled = true;
            record.disabled = false;
            var cancelAnimationFrame = window.cancelAnimationFrame || window.mozCancelAnimationFrame;
            cancelAnimationFrame(id_anim);
            clearInterval(handleDuration);
        };

        send.onclick = function() {
            //const blob = new Blob(chunks, { 'type' : 'audio/ogg; codecs=opus' });
            // upload each blob to PHP server
            if(blob !== undefined) uploadToPHPServer(blob);
            //chunks = [];
        };

        mediaRecorder.onstop = function(e) {
          console.log("data available after MediaRecorder.stop() called.");

          //const clipName = prompt('Enter a name for your sound clip?','My unnamed clip');

          const clipContainer = document.createElement('article');
          const clipLabel = document.createElement('p');
          const audio = document.createElement('audio');
          const deleteButton = document.createElement('button');

          clipContainer.classList.add('clip');
          audio.setAttribute('controls', '');
          deleteButton.textContent = 'Delete';
          deleteButton.className = 'delete';

                    /*
          if(clipName === null) {
            clipLabel.textContent = 'My unnamed clip';
          } else {
            clipLabel.textContent = clipName;
          }
              */
              clipLabel.textContent = 'My unnamed clip';
          clipContainer.appendChild(audio);
          clipContainer.appendChild(clipLabel);
          clipContainer.appendChild(deleteButton);
          soundClips.appendChild(clipContainer);

          audio.controls = true;
          blob = new Blob(chunks, { 'type' : 'audio/ogg; codecs=opus' });
          chunks = [];
          const audioURL = window.URL.createObjectURL(blob);
          audio.src = audioURL;
          console.log("recorder stopped");

          deleteButton.onclick = function(e) {
            let evtTgt = e.target;
            evtTgt.parentNode.parentNode.removeChild(evtTgt.parentNode);
          };

          clipLabel.onclick = function() {
            const existingName = clipLabel.textContent;
            const newClipName = prompt('Enter a new name for your sound clip?');
            if(newClipName === null) {
              clipLabel.textContent = existingName;
            } else {
              clipLabel.textContent = newClipName;
            }
          };
        };

        mediaRecorder.ondataavailable = function(e) {
          chunks.push(e.data);
        };
    };

    let onError = function(err) {
      console.log('The following error occured: ' + err);
    };

    navigator.mediaDevices.getUserMedia(constraints).then(onSuccess, onError);

} else {
    console.log('getUserMedia not supported on your browser!');
}

function addZero(n) {
    return ('0000'+n).match(/\d{2}$/);
}

function visualize(stream) {
    if(!audioCtx) {
      audioCtx = new AudioContext();
    }

    const source = audioCtx.createMediaStreamSource(stream);

    const analyser = audioCtx.createAnalyser();
    analyser.fftSize = 2048;
    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);

    source.connect(analyser);

    //analyser.connect(audioCtx.destination);

    draw();

    function draw() {
        const WIDTH = canvas.width;
        const HEIGHT = canvas.height;
        var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
                            window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
        id_anim = requestAnimationFrame(draw);

        analyser.getByteTimeDomainData(dataArray);

        canvasCtx.fillStyle = 'rgb(255, 255, 255)';
        canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

        canvasCtx.lineWidth = 2;
        canvasCtx.strokeStyle = 'rgb(0, 0, 0)';

        canvasCtx.beginPath();

        let sliceWidth = WIDTH * 1.0 / bufferLength;
        let x = 0;


        for(let i = 0; i < bufferLength; i++) {

          let v = dataArray[i] / 128.0;
          let y = v * HEIGHT/2;

          if(i === 0) {
                canvasCtx.moveTo(x, y);
          } else {
                canvasCtx.lineTo(x, y);
          }

          x += sliceWidth;
        }

        canvasCtx.lineTo(canvas.width, canvas.height/2);
        canvasCtx.stroke();

    }
}



window.onresize = function() {
  canvas.width = mainSection.offsetWidth;
};

window.onresize();



function uploadToPHPServer(blob) {
    var file = new File([blob], 'msr-' + (new Date).toISOString().replace(/:|\./g, '-') + '.webm', {
        type: 'video/webm'
    });

    // create FormData
    var formData = new FormData();
    formData.append('video-filename', file.name);
    formData.append('video-blob', file);

    makeXMLHttpRequest('save.php', formData, function() {
        //var downloadURL = 'https://path-to-your-server/uploads/' + file.name;
        console.log('File uploaded :'+ file.name);
    });
}

function makeXMLHttpRequest(url, data, callback) {
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
        if (request.readyState === 4 && request.status === 200) {
            callback();
        }
    };
    request.open('POST', url);
    request.send(data);
}