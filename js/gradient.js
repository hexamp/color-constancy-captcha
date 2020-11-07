  jQuery(function($){
    $("#picker").spectrum({
      color: "#000000", // Initial Value
      preferredFormat: "rgb"
    });
});

  let canvas = document.getElementById('canvas');
  let objHeight,objWidth;
  let x,y;
  let objX,objY,relX,relY;
  let dragging = false;
  let grad;
  let ctx = canvas.getContext('2d');
  let form = document.forms.captcha_form;
  let img = document.getElementById("captcha_img");

  const  over_h = Math.floor(Math.random()*360);
  const  over_s = Math.floor((Math.random()*30) + 70);
  const  over_l = Math.floor((Math.random()*30) + 70);

  window.onload = function(){
    if(canvas.getContext){
      objWidth = 10;
      objHeight = 10;
      objX = Math.floor(Math.random()*canvas.width);
      objY = Math.floor(Math.random()*canvas.height);

      if(objX + objWidth > canvas.width){
        objX = canvas.width - objWidth - 1;
      }

      if(objY + objHeight > canvas.height){
        objY = canvas.height - objHeight - 1;
      }

      setForm();
      makeCaptchaImg(ctx);
      setInterval("drawRect()",50);
    }
  };

  function onDown(e){
    let offsetX = canvas.getBoundingClientRect().left;
    let offsetY = canvas.getBoundingClientRect().top;
    x = e.clientX - offsetX;
    y = e.clientY - offsetY;

    if(x+objWidth/2 > canvas.width){
      x = canvas.width - objWidth/2;
    }
    else if(x-objWidth/2 < 0){
      x = objWidth/2 + 1;
    }

    if(y+objHeight/2 > canvas.height){
      y = canvas.height - objHeight/2;
    }
    else if(y-objHeight/2 < 0){
      y = objWidth/2 + 1;
    }

    if(x >= objX && x <= objX + objWidth && y >= objY && y <= objY+objHeight){
      dragging = true;
      relX = objX - x;
      relY = objY - y;
    }
    else{
      dragging = true;
      objX = x - 1- objWidth/2;
      objY = y - 1- objHeight/2;
      relX = objX - x;
      relY = objY - y;
      setForm();
    }
  }

  function onMove(e){
    let offsetX = canvas.getBoundingClientRect().left;
    let offsetY = canvas.getBoundingClientRect().top;
    x = e.clientX - offsetX;
    y = e.clientY - offsetY;


    if(x+objWidth/2 > canvas.width){
      x = canvas.width - objWidth/2;
    }
    else if(x-objWidth/2 < 0){
      x = objWidth/2 + 1;
    }

    if(y+objHeight/2 > canvas.height){
      y = canvas.height - objHeight/2;
    }
    else if(y-objHeight/2 < 0){
      y = objWidth/2 + 1;
    }

    if(dragging){
      objX = x + relX;
      objY = y + relY;
      setForm();
    }
  }

  function onUp(e){
    dragging = false;
  }

  function makeCaptchaImg(ctx){
    let canvas = ctx.canvas;

    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.drawImage(img,0,0);

    let grad = getGradientAngle(ctx);

    ctx.beginPath();
    initial_h = Math.floor(Math.random()*360);

    let gradation_num = 3;
    let increment_val = 1 / (gradation_num-1);
    let increment_angle = 360 / gradation_num;

    let s = 100;
    let v = 60;

    for(let i = 0; i < gradation_num; i++ ){
      grad.addColorStop(i * increment_val,'hsl(' + initial_h + (i * increment_angle) + ', '+ s + '%, ' + v + '%)');
    }
    /*
    grad.addColorStop(0,'hsl(' + initial_h + ', 100%, 60%)');
    grad.addColorStop(0.5,'hsl(' + (initial_h + 120) % 360 + ', 100%, 60%)');
    grad.addColorStop(1,'hsl(' + (initial_h + 240) % 360 + ', 100%, 60%)');
    */
    ctx.fillStyle = grad;
    ctx.globalAlpha = 0.5;
    ctx.rect(0,0,canvas.width,canvas.height);
    ctx.fill();

    let captcha_img = canvas.toDataURL();
    let task_img = document.getElementById("task_img")
    task_img.src = captcha_img;
  }

  function getGradientAngle(ctx){
    let angle = Math.floor(Math.random()*180);
    let angle_rad = angle * Math.PI / 180;

    let canvas = ctx.canvas;

    let diffx = Math.round((canvas.height/2) / Math.tan(angle_rad));

    if(diffx >= canvas.width/2){
      diffx = canvas.width/2;
    }

    let adjust_x1 = canvas.width/2 + diffx;
    let adjust_x2 = canvas.width/2 - diffx;

    let diffy = Math.round(diffx * Math.tan(angle_rad));
    let adjust_y1 = canvas.height/2 - diffy;
    let adjust_y2 = canvas.height/2 + diffy;

    if(adjust_x1 < 0){
      adjust_x1 = 0;
    }
    else if(adjust_x1 >= canvas.width){
      adjust_x1 = canvas.width;
    }

    if(adjust_x2 < 0){
      adjust_x2 = 0;
    }
    else if(adjust_x2 >= canvas.width){
      adjust_x2 = canvas.width;
    }

    if(adjust_y1 < 0){
      adjust_y1 = 0;
    }

    else if(adjust_y1 >= canvas.height){
      adjust_y1 = canvas.height;
    }

    if(adjust_y2 < 0){
      adjust_y2 = 0;
    }
    else if(adjust_y2 >= canvas.height){
      adjust_y2 = canvas.height;
    }
    return ctx.createLinearGradient(adjust_x2,adjust_y2,adjust_x1,adjust_y1);
  }

  function drawRect(){
    let ctx2 = ctx;
    let canvas2 = canvas;

    const task_img = document.getElementById("task_img");

    ctx2.clearRect(0,0,canvas2.width,canvas2.height);
    ctx2.drawImage(task_img,0,0);

    ctx2.globalAlpha = 1;
    ctx2.strokeRect(objX,objY,objWidth,objHeight);
  }

  function setForm(){
    form.objX.value = Math.floor(objX);
    form.objY.value = Math.floor(objY);
    form.objW.value = objX + objWidth;
    form.objH.value = objY + objHeight;

    let canvasData = $('canvas').get(0).toDataURL("image/jpeg");

    // Removed Extra Info;
    canvasData = canvasData.replace(/^data:image\/jpeg;base64,/, '');

    // Set canvas data to the canvas_img element for saving the experimental images.
    form.canvas_image.value = canvasData;
  }

  canvas.addEventListener('mousedown', onDown, false);
  canvas.addEventListener('mousemove', onMove, false);
  canvas.addEventListener('mouseup', onUp, false);
