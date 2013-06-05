(function($){
  var _global = this;

  var getTerminalId = function(iframe, callback) {
    var terminalName = $.cookie('toopher_terminal_name');
    if (terminalName) {
      callback(terminalName);
    } else {
      iframe.hide();
      // first time authenticating with Toopher from this terminal - prompt the user to name the terminal

      var tDiv = $('<div>Please enter a name for this terminal:<input type="text" id="toopher_terminal_name_input"></input><input id="toopher_terminal_name_button" type="button" value="OK"></input></div>');
      tDiv.children('#toopher_terminal_name_button').click(function(){
        terminalName = tDiv.children('#toopher_terminal_name_input').val();
        cookieOptions = { expires : 365, path: '/' };
        if (location.protocol === 'https:') {
          cookieOptions.secure = true;
        }
        $.cookie('toopher_terminal_name', terminalName, cookieOptions);
        tDiv.remove();
        iframe.show();
        callback(terminalName);
      });
      iframe.before(tDiv);
    }
    
  }

  var handleMessage = function(e){
    console.log('handled message');
    console.log(e.data);
    var iframe = $('#toopher_iframe');
    $.post(iframe.attr('toopher_postback'), e.data);
  }
  window.addEventListener('message', handleMessage, false);

  $(document).ready(function(){
    var iframe = $('#toopher_iframe');
    if (!iframe.length){
      // no toopher iframe present
    } else {
      init(iframe);
    }
  });

  var init = function(target){
    getTerminalId(target, function(terminalName){
      toopher_url = target.attr('toopher_req') + '&terminal_name=' + encodeURIComponent(terminalName);
      target.attr('src', toopher_url);
    });
  }

  this.init = init;
  return this;

})(jQuery)
