var toopher = (function($){
  var _global = this;

  var postToUrl = function (path, params, method){
    method = method || 'POST';
    var form = $('<form />').attr('method', method).attr('action', path);
    for (var key in params){
      if (params.hasOwnProperty(key)){
        console.log('adding form field "' + key + '" = "' + params[key] + '"');
        var hiddenField = $('<input />').attr('type', 'hidden').attr('name', key).attr('value', params[key]);
        form.append(hiddenField);
      }
    }
    $('body').append(form);
    form.submit();
  }

  var handleMessage = function(e){
    console.log('handled message');
    console.log(e.data);
    if (e.data.status === 'toopher-api-complete'){
      var iframe = $('#toopher_iframe');
      var frameworkPostArgsJSON = iframe.attr('framework_post_args');
      var frameworkPostArgs = {};
      if(frameworkPostArgsJSON){
        frameworkPostArgs = $.parseJSON(frameworkPostArgsJSON);
      }
      var postData = $.extend({}, e.data.payload, frameworkPostArgs);
      if(iframe.attr('use_ajax_postback')){
      $.post(iframe.attr('toopher_postback'), postData)
        .done(function(data){
          data = $.parseJSON(data);
        });
      } else {
        postToUrl(iframe.attr('toopher_postback'), postData, 'POST');
      }
    } else {
      console.log('unknown message type');
    }
  }
  window.addEventListener('message', handleMessage, false);

  var init = function(iframeSelector){
    var iframe = $(iframeSelector);
    if (!iframe.length){
      // no toopher iframe present
    } else {
      iframe.attr('src', iframe.attr('toopher_req'));
    }
  };

  var exports = {};
  exports.init = init;

  return exports;

})(jQuery);
