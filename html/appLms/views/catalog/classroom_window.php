<div id="myModal" class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalCenterTitle"><b><?=$course_name?></b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <div class="accordion" id="accordionMain">
            <?php foreach ($available_classrooms as $classroom_info) { ?>            
                  <div class="card">
                    <div class="card-header" id="heading<?=$classroom_info['id_date']?>">
                      <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?=$classroom_info['id_date']?>" aria-expanded="false" aria-controls="collapse<?=$classroom_info['id_date']?>">
                          <a href='#'><?=$classroom_info['name']?></a>
                        </button>
                        <?php if ($classroom_info['selling']) { ?>                
                            <?php if ($classroom_info['in_cart']) { ?>
                                <button type="button" class="btn btn-info"><?=Lang::t('_CLASSROOM_IN_CART', 'catalogue')?></button>
                            <?php } else  { ?>
                                <button type="button" class="btn btn-success">
                                        <?=Lang::t('_ADD_TO_CART', 'catalogue')?> (<?=$classroom_info['price']?>  <?=Get::sett('currency_symbol', '&euro;')?>)
                                </button>        
                            <?php } ?>
                        <?php } else { ?>
                            <?php if ($classroom_info['full'] ) {  ?>
                                    <?php if ($classroom_info['overbooking'] ) { ?>
                                        <button type="button" class="btn btn-info"><?=Lang::t('_USER_STATUS_OVERBOOKING', 'subscribe')?></button>
                                    <?php } else  { ?>
                                        <button type="button" class="btn btn-danger">
                                        <?=Lang::t('_MAX_NUM_SUBSCRIBE', 'course')?>
                                        </button>
                                    <?php } ?>                
                            <?php } else  { ?>
                                        <button type="button" class="btn btn-success"><?=Lang::t('_SUBSCRIBE', 'catalogue')?></button>
                            <?php } ?>                
                        <?php } ?>                
                      </h2>
                    </div>
                    <br>
                    <div id="collapse<?=$classroom_info['id_date']?>" class="collapse" aria-labelledby="heading<?=$classroom_info['id_date']?>" data-parent="#accordionMain">
                      <div class="card-body">
                        <div class="table-responsive">
                             <table class="table table-condensed"">
                                <thead>
                                    <tr>
                                        <th><?=Lang::t('_DATE', 'course')?></th>
                                        <th><?=Lang::t('_HOUR_BEGIN', 'course')?></th>
                                        <th><?=Lang::t('_HOUR_END', 'course')?></th>
                                        <th><?=Lang::t('_LOCATION', 'classroom')?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php  foreach($classroom_info['days'] as $day ) { ?>
                                    <tr>
                                    <td><?=Format::date($day['date_begin'], 'date')?></td>
                                    <td><?=Format::date($day['date_begin'], 'time')?></td>
                                    <td><?=Format::date($day['date_end'], 'time')?></td>
                                    <td><?=$day['classroom']?></td>
                                    </tr>
                                 <?php  } ?>
                                 </tbody>
                             </table>
                       </div>
                    </div> <!-- end card body -->     
                  </div> <!-- end card-->
                  <br>
            <?php } ?>                  
            </div> <!-- end accordion-->     
      </div> <!-- end modal body-->

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=Lang::t('_CLOSE')?></button>
      </div>
    </div>
  </div>
</div>