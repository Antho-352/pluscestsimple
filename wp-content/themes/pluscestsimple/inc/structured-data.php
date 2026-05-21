<?php
/**
 * Structured data per post/page: FAQ + HowTo repeaters.
 * Data is stored as JSON in post meta. Schema.php consumes it to emit JSON-LD.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ─── Register meta ────────────────────────────────────────────────────────────

add_action( 'init', function () {
	foreach ( [ '_arw_faq', '_arw_howto' ] as $key ) {
		register_post_meta( '', $key, [
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}
} );

// ─── Helpers ──────────────────────────────────────────────────────────────────

function arw_pulse_get_faq( int $post_id ): array {
	$raw = (string) get_post_meta( $post_id, '_arw_faq', true );
	if ( ! $raw ) { return []; }
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) { return []; }
	return array_values( array_filter( $data, fn( $r ) => ! empty( $r['q'] ) && ! empty( $r['a'] ) ) );
}

function arw_pulse_get_howto( int $post_id ): array {
	$raw = (string) get_post_meta( $post_id, '_arw_howto', true );
	if ( ! $raw ) { return []; }
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) { return []; }
	$title = isset( $data['title'] ) ? (string) $data['title'] : '';
	$steps = isset( $data['steps'] ) && is_array( $data['steps'] )
		? array_values( array_filter( $data['steps'], fn( $s ) => ! empty( $s['name'] ) || ! empty( $s['text'] ) ) )
		: [];
	if ( empty( $steps ) ) { return []; }
	return [ 'title' => $title, 'steps' => $steps ];
}

// ─── Meta box ─────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	$types = array_values( array_filter(
		get_post_types( [ 'public' => true ] ),
		fn( $t ) => ! in_array( $t, [ 'attachment', 'arw_product' ], true )
	) );
	add_meta_box( 'arw_structured', 'Données structurées (FAQ, Tuto)', 'arw_pulse_sd_box', $types, 'normal', 'default' );
} );

function arw_pulse_sd_box( WP_Post $post ): void {
	wp_nonce_field( 'arw_sd_save', 'arw_sd_nonce' );
	$faq   = arw_pulse_get_faq( $post->ID );
	$howto = arw_pulse_get_howto( $post->ID );
	$howto_title = $howto['title'] ?? '';
	$howto_steps = $howto['steps'] ?? [];
	?>
	<style>
		#arw_sd{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:13px}
		#arw_sd .sec{border:1px solid #dcdcde;border-radius:4px;padding:14px;margin-bottom:16px;background:#fff}
		#arw_sd h3{margin:0 0 4px;font-size:14px}
		#arw_sd .lead{color:#787c82;margin:0 0 12px;font-size:12px}
		#arw_sd .row{border:1px solid #e5e5e5;border-radius:3px;padding:10px;margin-bottom:8px;background:#fafafa;position:relative}
		#arw_sd .row label{display:block;font-weight:600;margin:0 0 3px;font-size:12px}
		#arw_sd .row input[type=text],#arw_sd .row textarea{width:100%;box-sizing:border-box;border:1px solid #8c8f94;border-radius:3px;padding:6px 8px;font-size:13px}
		#arw_sd .row textarea{min-height:56px;resize:vertical}
		#arw_sd .row .rm{position:absolute;top:8px;right:8px;background:transparent;border:0;color:#d63638;font-size:18px;cursor:pointer;padding:0;line-height:1}
		#arw_sd .row .rm:hover{color:#b32d2e}
		#arw_sd .row + .row{margin-top:6px}
		#arw_sd .add{background:#2271b1;color:#fff;border:0;border-radius:3px;padding:6px 14px;cursor:pointer;font-size:12px;font-weight:600}
		#arw_sd .add:hover{background:#135e96}
		#arw_sd .field{margin-bottom:10px}
		#arw_sd .field > label{display:block;font-weight:600;margin-bottom:4px}
		#arw_sd .field input[type=text]{width:100%;box-sizing:border-box;border:1px solid #8c8f94;border-radius:3px;padding:6px 8px}
	</style>

	<div id="arw_sd">

		<!-- FAQ -->
		<div class="sec">
			<h3>FAQ (FAQPage schema)</h3>
			<p class="lead">Chaque Q/R ajoutée ici est émise en <code>FAQPage</code> JSON-LD et éligible à un rich result Google.</p>

			<div id="arw_faq_list">
				<?php foreach ( $faq as $i => $r ) : ?>
					<div class="row">
						<button type="button" class="rm" aria-label="Supprimer">×</button>
						<label>Question</label>
						<input type="text" data-k="q" value="<?php echo esc_attr( $r['q'] ); ?>">
						<label style="margin-top:6px">Réponse</label>
						<textarea data-k="a"><?php echo esc_textarea( $r['a'] ); ?></textarea>
					</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="add" id="arw_faq_add">+ Ajouter une Q/R</button>
			<input type="hidden" name="arw_faq" id="arw_faq_json" value="<?php echo esc_attr( wp_json_encode( $faq ) ); ?>">
		</div>

		<!-- HowTo -->
		<div class="sec">
			<h3>Tuto étape par étape (HowTo schema)</h3>
			<p class="lead">Renseigne un titre global + des étapes. Émis en <code>HowTo</code> JSON-LD.</p>

			<div class="field">
				<label>Titre du tuto</label>
				<input type="text" id="arw_howto_title" placeholder="Ex. : Changer ses plaquettes de frein" value="<?php echo esc_attr( $howto_title ); ?>">
			</div>

			<div id="arw_howto_list">
				<?php foreach ( $howto_steps as $s ) : ?>
					<div class="row">
						<button type="button" class="rm" aria-label="Supprimer">×</button>
						<label>Nom de l'étape</label>
						<input type="text" data-k="name" value="<?php echo esc_attr( $s['name'] ?? '' ); ?>">
						<label style="margin-top:6px">Description</label>
						<textarea data-k="text"><?php echo esc_textarea( $s['text'] ?? '' ); ?></textarea>
					</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="add" id="arw_howto_add">+ Ajouter une étape</button>
			<input type="hidden" name="arw_howto" id="arw_howto_json" value="<?php echo esc_attr( wp_json_encode( [ 'title' => $howto_title, 'steps' => $howto_steps ] ) ); ?>">
		</div>

	</div>

	<script>
	(function(){
		function rowHtml(fields){
			var h='<div class="row"><button type="button" class="rm" aria-label="Supprimer">×</button>';
			fields.forEach(function(f){
				h+='<label'+(f.mt?' style="margin-top:6px"':'')+'>'+f.label+'</label>';
				h+=(f.ta?'<textarea data-k="'+f.k+'"></textarea>':'<input type="text" data-k="'+f.k+'">');
			});
			return h+'</div>';
		}
		function sync(listId, hiddenId, mapRow){
			var list=document.getElementById(listId),
				hidden=document.getElementById(hiddenId),
				rows=list.querySelectorAll('.row'),
				arr=[];
			rows.forEach(function(r){ arr.push(mapRow(r)); });
			hidden.value=JSON.stringify(arr);
		}
		function setupSection(opts){
			var list=document.getElementById(opts.listId),
				addBtn=document.getElementById(opts.addId),
				template=opts.fields;

			addBtn.addEventListener('click',function(){
				list.insertAdjacentHTML('beforeend', rowHtml(template));
				bindRows();
				opts.persist();
			});
			function bindRows(){
				list.querySelectorAll('.row').forEach(function(r){
					if(r.dataset.bound)return;
					r.dataset.bound='1';
					r.querySelector('.rm').addEventListener('click',function(){ r.remove(); opts.persist(); });
					r.querySelectorAll('[data-k]').forEach(function(el){
						el.addEventListener('input', opts.persist);
					});
				});
			}
			bindRows();
		}

		// FAQ
		var faqFields=[
			{label:'Question',k:'q'},
			{label:'Réponse',k:'a',ta:true,mt:true},
		];
		function faqPersist(){
			sync('arw_faq_list','arw_faq_json',function(r){
				return {
					q:(r.querySelector('[data-k=q]')||{}).value||'',
					a:(r.querySelector('[data-k=a]')||{}).value||''
				};
			});
		}
		setupSection({listId:'arw_faq_list', addId:'arw_faq_add', fields:faqFields, persist:faqPersist});

		// HowTo
		var howtoFields=[
			{label:'Nom de l\'étape',k:'name'},
			{label:'Description',k:'text',ta:true,mt:true},
		];
		function howtoPersist(){
			var list=document.getElementById('arw_howto_list'),
				rows=list.querySelectorAll('.row'),
				steps=[];
			rows.forEach(function(r){
				steps.push({
					name:(r.querySelector('[data-k=name]')||{}).value||'',
					text:(r.querySelector('[data-k=text]')||{}).value||''
				});
			});
			document.getElementById('arw_howto_json').value=JSON.stringify({
				title:document.getElementById('arw_howto_title').value||'',
				steps:steps
			});
		}
		setupSection({listId:'arw_howto_list', addId:'arw_howto_add', fields:howtoFields, persist:howtoPersist});
		document.getElementById('arw_howto_title').addEventListener('input', howtoPersist);
	})();
	</script>
	<?php
}

add_action( 'save_post', function ( int $post_id ): void {
	if ( empty( $_POST['arw_sd_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['arw_sd_nonce'] ) ), 'arw_sd_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	// FAQ
	$faq_raw = isset( $_POST['arw_faq'] ) ? wp_unslash( $_POST['arw_faq'] ) : '';
	$faq     = is_string( $faq_raw ) ? json_decode( $faq_raw, true ) : null;
	if ( is_array( $faq ) ) {
		$clean = [];
		foreach ( $faq as $r ) {
			$q = trim( sanitize_text_field( $r['q'] ?? '' ) );
			$a = trim( sanitize_textarea_field( $r['a'] ?? '' ) );
			if ( $q && $a ) { $clean[] = [ 'q' => $q, 'a' => $a ]; }
		}
		$clean ? update_post_meta( $post_id, '_arw_faq', wp_json_encode( $clean ) ) : delete_post_meta( $post_id, '_arw_faq' );
	}

	// HowTo
	$howto_raw = isset( $_POST['arw_howto'] ) ? wp_unslash( $_POST['arw_howto'] ) : '';
	$howto     = is_string( $howto_raw ) ? json_decode( $howto_raw, true ) : null;
	if ( is_array( $howto ) ) {
		$title = trim( sanitize_text_field( $howto['title'] ?? '' ) );
		$steps = [];
		foreach ( (array) ( $howto['steps'] ?? [] ) as $s ) {
			$n = trim( sanitize_text_field( $s['name'] ?? '' ) );
			$t = trim( sanitize_textarea_field( $s['text'] ?? '' ) );
			if ( $n || $t ) { $steps[] = [ 'name' => $n, 'text' => $t ]; }
		}
		if ( $title && $steps ) {
			update_post_meta( $post_id, '_arw_howto', wp_json_encode( [ 'title' => $title, 'steps' => $steps ] ) );
		} else {
			delete_post_meta( $post_id, '_arw_howto' );
		}
	}
} );
