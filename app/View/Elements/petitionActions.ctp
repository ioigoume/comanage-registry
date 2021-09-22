<script type="text/javascript">
$(function(){
  
  $("#notify-approver").click( function (){
    
    var url_str_statspercou = '<?php print $this->Html->url(array(
                                    'plugin' => NULL,
                                    'controller' => 'co_petitions',
                                    'action' => 'notifyapprovers',
                                    'co'  => 2,
                                )); ?>';
    // Don't allow user to click again while the operation is in progress                                
    $(this).prop('disabled', true);
    $(this).css('background-color','-internal-light-dark(rgb(239, 239, 239), rgb(59, 59, 59))')
    $(this).css('color','#ccc')
    $(this).css('cursor','default')
    $(this).css('border','#333')
    $(this).parent().find(".inprogress").show()
    let jqxhr = $.ajax({
            url: url_str_statspercou,
            data: {
                coef: $(this).data("coef"),
                ptid: $(this).data("ptid"),
            }
        });

    jqxhr.done((data) => {
      //$(this).prop("disabled", false);
      $(this).parent().find(".inprogress").hide();
      noty({
            text: 'A reminder sent to Approver(s) successfully',
            type: 'success',
            timeout: 2000,
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'comanage',
        })
    })
    jqxhr.fail((xhr, textStatus, error) => { handleFail(xhr, textStatus, error) })
  })
})
function handleFail(xhr, textStatus, error){
  // Show an error message
  // HTML Text
  let err_msg = $.parseHTML(xhr.responseText)[0].innerHTML;
  // JSON text
  try{
    //try to parse JSON
    encodedJson = $.parseJSON(xhr.responseJSON);
    err_msg = encodedJson.msg;
   
  }catch(error){
    // Plain text
    err_msg = xhr.responseText;
  }

  if(err_msg != null) {
    error = error + ': ' + err_msg;
  }
  generateFlash(error, textStatus);
}
</script>
<?php
if(!empty($vv_coef_next_step) && $status != PetitionStatusEnum::PendingApproval && $status != PetitionStatusEnum::Finalized && $status != PetitionStatusEnum::PendingConfirmation) {
  print $this->Html->link(
     _txt('op.resume'),
     (!empty($vv_done_step) ? 
     array(
      'controller' => 'co_petitions',
      'action' => $vv_coef_next_step,
      $co_petitions[0]['CoPetition']['id'],
      'done' => 'core'
      ) :
    array(
      'controller' => 'co_petitions',
      'action' => $vv_coef_next_step,
      $co_petitions[0]['CoPetition']['id']
      )
    ),
    array('class' => 'checkbutton approve-button')
  );
}
if($status == PetitionStatusEnum::PendingApproval) {
  if($permissions['isEnrollee']) {
    print "<button id='notify-approver' class='ui-button ui-widget' onclick='return false;'  data-ptid = \"".$co_petitions[0]['CoPetition']['id']."\" data-coef= \"".$co_petitions[0]['CoPetition']['co_enrollment_flow_id']."\">Notify Approver(s) Again</button><span class='inprogress' style='display:none'>&nbsp;Please wait...</span>";
  }
  else if($permissions['approve']) {
    print $this->Html->link(
      _txt('op.approve'),
      array(
        'controller' => 'co_petitions',
        'action' => 'approve',
        $co_petitions[0]['CoPetition']['id'],
        'co' => $co_petitions[0]['CoPetition']['co_id'],
        'coef' => $co_petitions[0]['CoPetition']['co_enrollment_flow_id']
      ),
      array('class' => 'checkbutton approve-button')
    );
  }
}

if(
  $status == PetitionStatusEnum::PendingApproval
  || $status == PetitionStatusEnum::PendingConfirmation
) {
  if($permissions['deny']) {
    print $this->Html->link(
      _txt('op.deny'),
      array(
        'controller' => 'co_petitions',
        'action' => 'deny',
        $co_petitions[0]['CoPetition']['id'],
        'co' => $co_petitions[0]['CoPetition']['co_id'],
        'coef' => $co_petitions[0]['CoPetition']['co_enrollment_flow_id']
      ),
      array('class' => 'cancelbutton deny-button')
    );
  }
  if($status == PetitionStatusEnum::PendingConfirmation && $permissions['view']) {
    $displayNameWithId = (!empty($co_petitions[0]['EnrolleeCoPerson']['PrimaryName']) ? generateCn($co_petitions[0]['EnrolleeCoPerson']['PrimaryName']) : _txt('fd.enrollee.new')) . ' (' . $co_petitions[0]['CoPetition']['status'] . ')';
    if($permissions['resend']) {
      $url = array(
        'controller' => 'co_petitions',
        'action' => 'resend',
        $co_petitions[0]['CoPetition']['id']
      );

      $options = array();
      $options['class'] = 'invitebutton';
      $options['onclick'] = "javascript:js_confirm_generic('" . _jtxt(_txt('op.inv.resend.confirm', array(filter_var(generateCn($co_petitions[0]['EnrolleeCoPerson']['PrimaryName']), FILTER_SANITIZE_SPECIAL_CHARS)))) . "', '"
        . Router::url($url) . "', '"
        . _txt('op.inv.resend') . "');return false";
      $options['title'] = _txt('op.inv.resend.to', array($displayNameWithId));
      $options['aria-label'] = _txt('op.inv.resend.to', array($displayNameWithId));

      print $this->Html->link(
        _txt('op.inv.resend'),
        $url,
        $options
      ) . "\n";
    }
  }
}
?>
