$(document).ready(function() {
	
	$(window).scroll(function () {
		var element = $(".toTop");
		if($(this).scrollTop() > 100)
			element.fadeIn("normal");
		else
			element.fadeOut("normal"); 		
    })
	
	
	$(this).delegate('.hreq', 'click', function(e){
		e.preventDefault();
		
		load({
			request: $(this).attr("href"),
			format: 'html',
			error_element: 'error',
			loading_element: 'loading'		
		});
		
	});
	
	$(this).delegate('.freq', 'submit', function(e){
		e.preventDefault();
		
		load({
			type: 'post',
			data: $(this).serialize(),
			request: $(this).attr("action"),
			format: 'html',
			error_element: 'error',
			loading_element: 'loading'
		});
		
	});
	
	
	$(this).delegate('.toggle_commits', 'click', function(e){
		e.preventDefault();
		
		$('#commits .list').slideToggle('fast', function(){
			$.ajax({
				url: '/guias/toggle/format/json',
				dataType: 'json'
			});
		});
		$(this).toggleClass('commits_show');
		
	});
	
	$(this).delegate('.toTop', 'click', function(e){
		e.preventDefault();
		
		$.scrollTo(0, 800, { queue:true });
		
	});
	
	$('.autoload').trigger('click');
	$('.hide').hide();
	
});
	
	 var load = function(params){
		
		var controller  = params.request.split('/')[1];
		var scrollto    = $('#' + controller + '_slice');
		var response    = $('#' + controller + '_response');
		var loading     = $('.' + params.loading_element);
		var error       = $('.' + params.error_element);
		
		$('<div class="' + params.loading_element + '"></div>').appendTo(response);
		$('.ckeditor').ckeditor(function(){ this.destroy(); })
		
		var async = $.ajax({
						type: params.type,			
						url: params.request + '?format=' + params.format,
						data: params.data,
						contentType: params.contentType,
						processData: params.processData
					})
		
		async.done(function(data){
			error.remove();
			response.html(data);
			$.scrollTo(scrollto.position().top - 110, 800, { queue:true });
			
			$('.ckeditor').ckeditor({
				width: '860px',
				height: '300px',
				toolbar : [
					{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
					{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
					{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
					{ name: 'insert', items : [ 'Table','HorizontalRule','SpecialChar' ] },
					{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] },
					'/',
					{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
					{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
					{ name: 'styles', items : [ 'Styles','Format','FontSize' ] },
					{ name: 'colors', items : [ 'TextColor','BGColor' ] }
				]
			});
			
		})
		.fail(function() {
			error.remove();
			$('<div class="' + params.error_element + '"></div>').appendTo('.title_action');
		})
		.always(function() {
			loading.remove();
		});
		
		
	}
	

