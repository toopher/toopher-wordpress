(function($, dest){

  var state = null;
  var container = null;
  var statusContainer = null;
  var toopherWebApi = null;

  var init = function(_toopherWebApi, _targetId, _statusId, _initialState){
    toopherWebApi = _toopherWebApi;
    state = _initialState;
    container = $('#' + _targetId);
    statusContainer = $('#' + _statusId);
    statusContainer.text(_initialState);
    updateUI();
  }

  var updateUI = function() {
    if(state === 'paired') {
    } else if (state === 'unpaired'){
      showPairingFrame();
    }
  }

  var showPairingFrame = function(){
    var content = $('<div><input type="button" id="toopher_init_pairing" class="button toopher_button" value="Pair This Account" ></input><div id="toopher_iframe_container"></div></div>');
    content.children('#toopher_init_pairing').click(function(){
      // get the link
      $.getJSON(ajaxurl, 
        data = {
          'action': 'toopher_get_pair_url_for_current_user'
        }, 
        success = function(response) {
          debugger;
          var iframe = $('<iframe />').attr('toopher_req', response.toopher_req).attr('toopher_postback', response.toopher_postback);
          content.children('#toopher_iframe_container').append(iframe);
          toopherWebApi.init(iframe);
        }
      );

    });
    container.append(content);

    
  }

  var exports = {};
  exports.init = init;

  return exports;

})(jQuery, window)
