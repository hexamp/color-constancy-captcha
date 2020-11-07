function canvasimage(){
  var data = {};
  //alert("a");
  // Canvasのデータをbase64でエンコードした文字列を取得
  var canvasData = $('canvas').get(0).toDataURL();



  // 不要な情報を取り除く
  canvasData = canvasData.replace(/^data:image\/png;base64,/, '');
  // canvasData = canvasData.replace(' ','+');

  //alert(canvasData);

const canvas_image = document.captcha_form.canvas_image;

canvas_image.value = canvasData;

}
