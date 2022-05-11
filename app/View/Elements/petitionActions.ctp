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
$pt_statuses = array(
                      PetitionStatusEnum::PendingApproval,
                      PetitionStatusEnum::Finalized,
                      PetitionStatusEnum::PendingConfirmation,
                      PetitionStatusEnum::Denied,
                      PetitionStatusEnum::Declined,
                      PetitionStatusEnum::Duplicate
                    );
if($permissions['isEnrollee'] && !empty($vv_coef_next_step) && !in_array($status, $pt_statuses)) {
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

if(
  $status == PetitionStatusEnum::PendingApproval
  || $status == PetitionStatusEnum::PendingConfirmation
) {
  // We create a form here as part of CO-1658, since it's cleaner than
  // grabbing the comment and stuffing it into a GET paramater on submit.
  // For now this isn't a problem because even though we're in fields.inc,
  // petitions only get rendered via view, not edit. However, whenever CO-431
  // (edit petitions) gets implemented this would change. At that time
  // this may need to be reimplemented, possibly with the use of the HTML5
  // "form" attribute eg <input type="text" form="approverForm" ...>
  // (browser support should be better by then), or by simply only allowing
  // approval/denial when viewing (not editing) the petition.
  if($permissions['approve']) {
    $args = array(
      'type' => 'post',
      'url' => array(
        'controller' => 'co_petitions', 
        'action' => 'approve',
        $co_petitions[0]['CoPetition']['id']
      )
    );

    print $this->Form->create('CoPetition', $args);

    if($co_petitions[0]['CoPetition']['status'] == PetitionStatusEnum::PendingApproval) {
      print $this->Form->submit(_txt('op.approve'),
                                array(
                                  'class' => 'checkbutton approve-button',
                                  'name'  => 'action')
      );
    }
    print $this->Form->submit(_txt('op.deny'),
                              array(
                                'class' => 'cancelbutton deny-button',
                                'name'  => 'action')
    );

    print $this->Form->textarea('approver_comment',
                              array(
                                'label' => _txt('fd.pt.approver_comment'),
                                'placeholder' => _txt('en.required', null, RequiredEnum::Optional),
                                'size' => 4000
                            ));

    print '<div class="field-desc">' . _txt('fd.pt.approver_comment.desc') . '</div>';
    print $this->Form->end();
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
