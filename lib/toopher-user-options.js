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
    var content = $('<div><input type="button" id="toopher-init-pairing" class="button toopher-button" value="Pair This Account" ></input><div id="toopher-iframe-container"></div></div>');
    content.children('#toopher-init-pairing').click(function(){
      // get the link
      $.getJSON(ajaxurl, 
        data = {
          'action': 'toopher_get_pair_url_for_current_user'
        }, 
        success = function(response) {
          var iframe = $('<iframe />').attr('toopher-req', response.toopher_req).attr('toopher-postback', response.toopher_postback);
          content.children('#toopher-iframe-container').append(iframe);
          debugger;
          toopherWebApi.init(iframe);
        }
      );

    });
    container.append(content);

    
  }

  var exports = {};
  exports.init = init;
  dest.toopherUserOptions = exports;

})(jQuery, window)
