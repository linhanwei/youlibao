wx.config({
      debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
      appId: '<{$wxjsinfo.appId}>', // 必填，公众号的唯一标识
      timestamp: <{$wxjsinfo.timestamp}>, // 必填，生成签名的时间戳
      nonceStr: '<{$wxjsinfo.nonceStr}>', // 必填，生成签名的随机串
      signature: '<{$wxjsinfo.signature}>',// 必填，签名，见附录1
      jsApiList: [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onVoiceRecordEnd',
        'playVoice',
        'onVoicePlayEnd',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard'
      ]
  });

function convertImgToBase64(url, callback, outputFormat){ 
  var canvas = document.createElement('CANVAS'); 
  var ctx = canvas.getContext('2d'); 
  var img = new Image; 
  img.crossOrigin = 'Anonymous'; 
  img.onload = function(){ 
    canvas.height = img.height; 
    canvas.width = img.width; 
    ctx.drawImage(img,0,0); 
    var dataURL = canvas.toDataURL(outputFormat || 'image/png'); 
    callback.call(this, dataURL); 
    canvas = null; 
  }; 
  img.src = url; 
} 