  jQuery(function($){
    $("#picker").spectrum({
      color: "#000000",
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

    const back_h = Math.floor(Math.random()*360);
    const s = 100;
    const v = 60;

    ctx.fillStyle = 'hsl(' + back_h + ', ' + s + '%, ' + v + '%)';
    ctx.globalAlpha = 0.3;
    ctx.fillRect(0,0,canvas.width,canvas.height);

    let obj_num = 100;

    let size = 40; //radius size
    let poly = 10; //
    let region = Math.floor(360 / poly);
    let rnd_size = 0;
    let start_x = 0;
    let start_y = 0;

    for(let i = 0 ; i < obj_num; i++){
      start_x = Math.floor(Math.random()*canvas.width);
      start_y = Math.floor(Math.random()*canvas.height);
      ctx.beginPath();
    
      for(let j = 0; j < poly; j++){
        let offset  = region * j + 1;
        rnd_size = Math.floor(Math.random()*size);
        rnd_angle = (Math.floor(Math.random()*region) + offset) * Math.PI/180;
        if(j == 0){
          ctx.moveTo(start_x + rnd_size*Math.cos(rnd_angle), start_y + rnd_size*Math.sin(rnd_angle));
        }
        else{
          ctx.lineTo(start_x + rnd_size*Math.cos(rnd_angle), start_y + rnd_size*Math.sin(rnd_angle));
        }
      }

      let h = Math.floor(Math.random()*360);

      ctx.closePath();
      ctx.fillStyle = 'hsl(' + h + ', ' + s + '%, ' + v + '%)';
      ctx.globalAlpha = 0.3;
      ctx.fill();
    }
    

    let captcha_img = canvas.toDataURL();
    let task_img = document.getElementById("task_img")
    task_img.src = captcha_img;
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
