	<ul class="meta_control" id="jobtitle_metabox">
    <li>
        <?php $metabox->the_field('_team_position'); ?>
            <label>Position within (((ASF)))</label>
            <div class="input_container">
                <input type="text" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>">
           </div>
        </li>
    <li>
        <?php $metabox->the_field('_team_org'); ?>
            <label>University or Organization</label>
            <div class="input_container">
                <input type="text" value="<?php $metabox->the_value(); ?>" id="<?php $metabox->the_name(); ?>" name="<?php $metabox->the_name(); ?>">
           </div>
        </li>
     </ul>