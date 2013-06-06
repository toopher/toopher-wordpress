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
    showPairingFrame();
  }

  var update = function(data) {
    state = data['paired'] ? 'paired' : 'unpaired';
    showPairingFrame();
  }

  var showPairingFrame = function(){
    var content;
    if (state === 'paired'){
      content = $('<div><input type="button" id="toopher_init_pairing" class="button toopher_button" value="Unpair This Account" ></input><div id="toopher_iframe_container"></div></div>');
    } else {
      content = $('<div><input type="button" id="toopher_init_pairing" class="button toopher_button" value="Pair This Account" ></input><div id="toopher_iframe_container"></div></div>');
    }
    content.children('#toopher_init_pairing').click(function(){
      // get the link
      $.getJSON(ajaxurl, 
        data = {
          'action': 'toopher_get_pair_url_for_current_user'
        }, 
        success = function(response) {
          var iframe = $('<iframe id="toopher_iframe" />').attr('toopher_req', response.toopher_req).attr('toopher_postback', ajaxurl).attr('framework_post_args', response.framework_post_args);
          content.children('#toopher_iframe_container').append(iframe);
          toopherWebApi.init(iframe, update);
        }
      );

    });
    container.empty();
    container.append(content);

    
  }

  var exports = {};
  exports.init = init;

  return exports;

})(jQuery, window)
