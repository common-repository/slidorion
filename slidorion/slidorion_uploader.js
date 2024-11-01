var current_slidorion_slide = '';

function send_to_editor(html) {
	var source = html.match(/src=\".*\" alt/);
	source = source[0].replace(/^src=\"/, "").replace(/" alt$/, "");
	jQuery('#'+current_slidorion_slide).val(source);
	jQuery('#img_preview_'+current_slidorion_slide).attr('src', source).css('background', 'none');
	tb_remove();
}

function toggleImageHolder() {
	jQuery(this).next('div').slideToggle();
}

function addNewImageHolder() {
	slidorion_imageholder_num++;
	jQuery(this).before('<div class="image-holder"><h3>Slide '+slidorion_imageholder_num+'</h3><div><a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox" onclick="current_slidorion_slide=\'slide'+slidorion_imageholder_num+'\';"><img id="img_preview_slide'+slidorion_imageholder_num+'" class="img-preview" src="'+slidorion_image_placeholder+'" /></a><input type="hidden" id="slide'+slidorion_imageholder_num+'" name="slidorion_slides[slide'+slidorion_imageholder_num+']"><input type="text" name="slidorion_titles[slide'+slidorion_imageholder_num+']" /><textarea name="slidorion_accords[slide'+slidorion_imageholder_num+']" id="accordion_text_slide'+slidorion_imageholder_num+'"></textarea><a href="#" class="clear">Clear</a><a href="#" class="remove">Remove</a></div></div>');
}

function clearImageHolder() {
	jQuery(this).parent().parent().find(':input').each(function() {
		jQuery(this).val('');
	});
	jQuery(this).parent().parent().find('.img-preview').attr('src', slidorion_image_placeholder);
}

function removeImageHolder() {
	slidorion_imageholder_num--;
	jQuery(this).parent().parent().remove();
	return false;
}

jQuery(document).ready(function() {
	jQuery('.image-holder h3').live('click', toggleImageHolder);
	jQuery('.add-image-holder').live('click', addNewImageHolder);
	jQuery('.image-holder a.clear').live('click', clearImageHolder);
	jQuery('.image-holder a.remove').live('click', removeImageHolder);
})