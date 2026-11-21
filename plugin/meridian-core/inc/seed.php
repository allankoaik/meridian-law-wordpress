<?php
/**
 * Demo content installer. Run once with meridian_seed() (the Playground blueprint does this).
 * Creates English + Spanish pages, practice areas, attorneys, state/city locations and articles,
 * links each pair as translations in Polylang, and sets the homepage and article page.
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------- Block markup helpers */

function mx_p( $text, $class = '' ) {
	$attrs = $class ? ' {"className":"' . $class . '"}' : '';
	$cls   = $class ? ' class="' . $class . '"' : '';
	return "<!-- wp:paragraph$attrs -->\n<p$cls>$text</p>\n<!-- /wp:paragraph -->\n\n";
}

function mx_h( $text, $level = 2 ) {
	$attrs = 2 === $level ? '' : ' {"level":' . $level . '}';
	return "<!-- wp:heading$attrs -->\n<h$level class=\"wp-block-heading\">$text</h$level>\n<!-- /wp:heading -->\n\n";
}

function mx_list( $items, $class = '' ) {
	$attrs = $class ? ' {"className":"' . $class . '"}' : '';
	$cls   = $class ? ' class="wp-block-list ' . $class . '"' : ' class="wp-block-list"';
	$li    = '';
	foreach ( $items as $item ) {
		$li .= "<!-- wp:list-item -->\n<li>$item</li>\n<!-- /wp:list-item -->\n";
	}
	return "<!-- wp:list$attrs -->\n<ul$cls>$li</ul>\n<!-- /wp:list -->\n\n";
}

function mx_sc( $code ) {
	return "<!-- wp:shortcode -->\n$code\n<!-- /wp:shortcode -->\n\n";
}

function mx_buttons( $buttons ) {
	$inner = '';
	foreach ( $buttons as $b ) {
		list( $label, $url, $style ) = $b;
		$inner .= "<!-- wp:button {\"className\":\"is-style-$style\"} -->\n<div class=\"wp-block-button is-style-$style\"><a class=\"wp-block-button__link wp-element-button\" href=\"$url\">$label</a></div>\n<!-- /wp:button -->\n";
	}
	return "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\">$inner</div>\n<!-- /wp:buttons -->\n\n";
}

function mx_section( $inner, $class, $full = true ) {
	$align = $full ? ',"align":"full"' : '';
	$acls  = $full ? ' alignfull' : '';
	return "<!-- wp:group {\"className\":\"$class\"$align,\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group $class$acls\">$inner</div>\n<!-- /wp:group -->\n\n";
}

/* ---------------------------------------------------------------- Insert helpers */

function mx_insert( $type, $title, $slug, $content, $lang, $extra = array(), $meta = array() ) {
	$id = wp_insert_post( array_merge( array(
		'post_type'    => $type,
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_author'  => 1,
	), $extra ) );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( $meta as $key => $value ) {
		update_post_meta( $id, $key, $value );
	}
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $id, $lang );
	}
	return $id;
}

function mx_link( $en, $es ) {
	if ( $en && $es && function_exists( 'pll_save_post_translations' ) ) {
		pll_save_post_translations( array( 'en' => $en, 'es' => $es ) );
	}
}

/* ---------------------------------------------------------------- Polylang */

function meridian_setup_polylang() {
	if ( ! function_exists( 'PLL' ) || ! PLL() || empty( PLL()->model ) ) {
		return false;
	}
	$model = PLL()->model;
	if ( ! $model->get_language( 'en' ) ) {
		$model->add_language( array( 'name' => 'English', 'slug' => 'en', 'locale' => 'en_US', 'rtl' => false, 'term_group' => 0, 'flag' => 'us' ) );
	}
	if ( ! $model->get_language( 'es' ) ) {
		$model->add_language( array( 'name' => 'Español', 'slug' => 'es', 'locale' => 'es_MX', 'rtl' => false, 'term_group' => 1, 'flag' => 'mx' ) );
	}
	if ( method_exists( $model, 'clean_languages_cache' ) ) {
		$model->clean_languages_cache();
	}

	$settings = array(
		'default_lang' => 'en',
		'force_lang'   => 1, // Language set from the directory name: /es/...
		'hide_default' => 1, // English URLs have no /en/ prefix.
		'rewrite'      => 1,
		'browser'      => 0,
		'redirect_lang' => 1, // Spanish homepage lives at /es/ rather than /es/inicio/.
		'media_support' => 0,
		'post_types'   => array( 'practice_area', 'attorney', 'location' ),
	);
	$options = PLL()->options;
	if ( is_array( $options ) ) {
		$options = array_merge( $options, $settings );
		update_option( 'polylang', $options );
		PLL()->options = $options;
	} else {
		foreach ( $settings as $key => $value ) {
			$options[ $key ] = $value;
		}
		if ( method_exists( $options, 'save' ) ) {
			$options->save();
		}
	}
	return true;
}

/* ---------------------------------------------------------------- Seeder */

function meridian_seed() {
	if ( get_option( 'meridian_seeded' ) ) {
		return 'already seeded';
	}
	meridian_register_post_types();
	$pll = meridian_setup_polylang();

	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'blogname', 'Meridian Injury Law' );
	update_option( 'blogdescription', 'Injury lawyers for Texas and Louisiana' );

	// Remove default "Hello world" / "Sample Page" content.
	foreach ( get_posts( array( 'post_type' => array( 'post', 'page' ), 'numberposts' => -1, 'post_status' => 'any' ) ) as $old ) {
		wp_delete_post( $old->ID, true );
	}

	$es = $pll ? '/es' : '';
	$u  = array(
		'en' => array( 'contact' => home_url( '/contact/' ), 'pa' => home_url( '/practice-areas/' ), 'about' => home_url( '/about/' ) ),
		'es' => array( 'contact' => home_url( "$es/contacto/" ), 'pa' => home_url( "$es/practice-areas/" ), 'about' => home_url( "$es/nosotros/" ) ),
	);

	/* ---------- Practice areas */
	$areas = array(
		array(
			'icon' => 'car',
			'en'   => array( 'Car Accidents', 'car-accidents', 'Injured by a careless or distracted driver? We handle the insurance companies, the medical bills and the paperwork so you can focus on recovery.',
				array( 'Rear-end and intersection crashes', 'Uninsured and underinsured drivers', 'Rideshare (Uber and Lyft) accidents', 'Pedestrian and bicycle collisions' ),
				'We collect the police report, photos, witness statements and medical records within days, not months. Then we build a demand that reflects your real losses: medical care, lost wages, and the pain you live with.' ),
			'es'   => array( 'Accidentes de auto', 'accidentes-de-auto', '¿Le lesionó un conductor descuidado o distraído? Nosotros tratamos con las aseguradoras, las facturas médicas y el papeleo para que usted se enfoque en recuperarse.',
				array( 'Choques por detrás y en intersecciones', 'Conductores sin seguro o con seguro insuficiente', 'Accidentes de Uber y Lyft', 'Atropellos de peatones y ciclistas' ),
				'Reunimos el reporte policial, fotos, testigos y expedientes médicos en días, no meses. Después preparamos una demanda que refleja sus pérdidas reales: atención médica, salarios perdidos y el dolor con el que vive.' ),
		),
		array(
			'icon' => 'truck',
			'en'   => array( 'Truck Accidents', 'truck-accidents', 'Commercial trucking cases move fast and involve large corporations. We act immediately to preserve black-box data, driver logs and maintenance records.',
				array( '18-wheeler and semi-truck crashes', 'Delivery and box-truck accidents', 'Driver fatigue and hours-of-service violations', 'Unsafe loading and equipment failure' ),
				'Trucking companies send investigators to the scene within hours. We send ours too, and we hold every responsible party accountable: the driver, the carrier, the shipper and the maintenance company.' ),
			'es'   => array( 'Accidentes de camiones', 'accidentes-de-camiones', 'Los casos de camiones comerciales avanzan rápido e involucran grandes empresas. Actuamos de inmediato para conservar la caja negra, los registros del conductor y el mantenimiento.',
				array( 'Choques de tráileres y camiones de 18 ruedas', 'Accidentes de camiones de reparto', 'Fatiga del conductor y violaciones de horas de servicio', 'Carga insegura y fallas del equipo' ),
				'Las compañías de transporte envían investigadores en pocas horas. Nosotros también, y responsabilizamos a todas las partes: el conductor, la empresa, el remitente y el taller de mantenimiento.' ),
		),
		array(
			'icon' => 'helmet',
			'en'   => array( 'Workplace Injuries', 'workplace-injuries', 'Hurt on the job at a refinery, construction site or warehouse? You may have options beyond workers’ compensation, especially in Texas.',
				array( 'Refinery and plant explosions', 'Construction falls and scaffolding accidents', 'Oilfield injuries', 'Non-subscriber employer claims in Texas' ),
				'Many Texas employers opt out of workers’ compensation. When they do, you can sue them directly for negligence. We explain your options clearly, in English or Spanish, before you sign anything.' ),
			'es'   => array( 'Lesiones en el trabajo', 'lesiones-en-el-trabajo', '¿Se lesionó trabajando en una refinería, una obra o un almacén? Puede tener opciones además de la compensación laboral, sobre todo en Texas.',
				array( 'Explosiones en refinerías y plantas', 'Caídas en construcción y andamios', 'Lesiones en campos petroleros', 'Reclamos contra empleadores no suscritos en Texas' ),
				'Muchos empleadores en Texas no tienen compensación laboral. En ese caso, puede demandarlos directamente por negligencia. Le explicamos sus opciones con claridad, en español o en inglés, antes de que firme nada.' ),
		),
		array(
			'icon' => 'heart',
			'en'   => array( 'Wrongful Death', 'wrongful-death', 'Losing a loved one because of someone else’s negligence is devastating. We handle the legal fight with compassion so your family can grieve.',
				array( 'Fatal car and truck crashes', 'Deadly workplace accidents', 'Medical negligence', 'Claims for spouses, children and parents' ),
				'A wrongful death claim can cover funeral costs, lost income and the loss of companionship. Our team meets families at home, in their language, and keeps them informed at every step.' ),
			'es'   => array( 'Muerte injusta', 'muerte-injusta', 'Perder a un ser querido por la negligencia de otra persona es devastador. Llevamos la lucha legal con compasión para que su familia pueda vivir su duelo.',
				array( 'Accidentes fatales de auto y camión', 'Accidentes laborales mortales', 'Negligencia médica', 'Reclamos para cónyuges, hijos y padres' ),
				'Un reclamo por muerte injusta puede cubrir gastos funerarios, ingresos perdidos y la pérdida de compañía. Visitamos a las familias en su casa, en su idioma, y las mantenemos informadas en cada paso.' ),
		),
	);

	$labels = array(
		'en' => array( 'handle' => 'What we handle', 'help' => 'How we help', 'cta' => 'Talk to a lawyer today', 'free' => 'Free Consultation' ),
		'es' => array( 'handle' => 'Qué casos atendemos', 'help' => 'Cómo le ayudamos', 'cta' => 'Hable con un abogado hoy', 'free' => 'Consulta gratis' ),
	);

	foreach ( $areas as $i => $area ) {
		$ids = array();
		foreach ( array( 'en', 'es' ) as $lang ) {
			list( $title, $slug, $intro, $list, $help ) = $area[ $lang ];
			$content = mx_p( $intro, 'mx-lead' )
				. mx_h( $labels[ $lang ]['handle'] ) . mx_list( $list, 'mx-checks' )
				. mx_h( $labels[ $lang ]['help'] ) . mx_p( $help );
			$ids[ $lang ] = mx_insert( 'practice_area', $title, $slug, $content, $lang, array( 'menu_order' => $i, 'post_excerpt' => $intro ), array( '_meridian_icon' => $area['icon'] ) );
		}
		mx_link( $ids['en'], $ids['es'] );
	}

	/* ---------- Attorneys */
	$attorneys = array(
		array( 'MD', 'en' => array( 'María Delgado', 'maria-delgado', 'Founding Partner · Board Certified, Personal Injury Trial Law',
			'María founded Meridian after fifteen years defending insurance companies, so she knows exactly how they value claims. She has tried more than 60 cases to verdict and leads the firm’s trucking practice.',
			array( 'J.D., University of Texas School of Law', 'Licensed in Texas and Louisiana', 'Fluent in English and Spanish' ) ),
			'es' => array( 'María Delgado', 'abogada-maria-delgado', 'Socia fundadora · Certificada en litigio de lesiones personales',
			'María fundó Meridian después de quince años defendiendo aseguradoras, así que sabe exactamente cómo valoran los reclamos. Ha llevado más de 60 casos a veredicto y dirige el área de accidentes de camiones.',
			array( 'Doctorado en Derecho, Universidad de Texas', 'Licencia en Texas y Luisiana', 'Habla español e inglés con fluidez' ) ) ),
		array( 'JC', 'en' => array( 'James Carter', 'james-carter', 'Partner · Workplace and Industrial Injuries',
			'James grew up in a refinery town and has spent his career representing injured workers. He is known for explaining complex cases in plain language and for preparing every case as if it will go to trial.',
			array( 'J.D., Tulane University Law School', 'Licensed in Texas and Louisiana', 'Former member of the state trial lawyers’ board' ) ),
			'es' => array( 'James Carter', 'abogado-james-carter', 'Socio · Lesiones laborales e industriales',
			'James creció en un pueblo de refinerías y ha dedicado su carrera a representar a trabajadores lesionados. Es conocido por explicar casos complejos con palabras sencillas y por preparar cada caso como si fuera a juicio.',
			array( 'Doctorado en Derecho, Universidad Tulane', 'Licencia en Texas y Luisiana', 'Exmiembro de la junta estatal de abogados litigantes' ) ) ),
		array( 'AN', 'en' => array( 'Ana Nguyen', 'ana-nguyen', 'Associate Attorney · Car Accidents and Wrongful Death',
			'Ana guides families through the hardest moments of their lives. She handles car crash and wrongful death claims and makes sure every client gets a call back the same day.',
			array( 'J.D., University of Houston Law Center', 'Licensed in Texas', 'Speaks English, Spanish and Vietnamese' ) ),
			'es' => array( 'Ana Nguyen', 'abogada-ana-nguyen', 'Abogada asociada · Accidentes de auto y muerte injusta',
			'Ana acompaña a las familias en los momentos más difíciles de su vida. Atiende reclamos por accidentes de auto y muerte injusta, y se asegura de que cada cliente reciba una llamada el mismo día.',
			array( 'Doctorado en Derecho, Universidad de Houston', 'Licencia en Texas', 'Habla inglés, español y vietnamita' ) ) ),
	);
	$bio_labels = array( 'en' => 'Education and credentials', 'es' => 'Educación y credenciales' );

	foreach ( $attorneys as $i => $person ) {
		$ids = array();
		foreach ( array( 'en', 'es' ) as $lang ) {
			list( $name, $slug, $role, $bio, $creds ) = $person[ $lang ];
			$content      = mx_p( $bio, 'mx-lead' ) . mx_h( $bio_labels[ $lang ] ) . mx_list( $creds, 'mx-checks' );
			$ids[ $lang ] = mx_insert( 'attorney', $name, $slug, $content, $lang, array( 'menu_order' => $i, 'post_excerpt' => wp_trim_words( $bio, 20 ) ), array( '_meridian_role' => $role, '_meridian_initials' => $person[0] ) );
		}
		mx_link( $ids['en'], $ids['es'] );
	}

	/* ---------- Locations: state > city */
	$states = array(
		array(
			'en' => array( 'Texas', 'texas', 'Offices in Houston, Dallas and San Antonio, serving injured people and families across Texas.' ),
			'es' => array( 'Texas', 'oficinas-texas', 'Oficinas en Houston, Dallas y San Antonio, al servicio de personas lesionadas y familias en todo Texas.' ),
			'cities' => array(
				array( 'en' => array( 'Houston', 'houston', '1200 Smith Street, Suite 1800, Houston, TX 77002' ), 'es' => array( 'Houston', 'oficina-houston', '1200 Smith Street, Suite 1800, Houston, TX 77002' ) ),
				array( 'en' => array( 'Dallas', 'dallas', '2100 Ross Avenue, Suite 900, Dallas, TX 75201' ), 'es' => array( 'Dallas', 'oficina-dallas', '2100 Ross Avenue, Suite 900, Dallas, TX 75201' ) ),
				array( 'en' => array( 'San Antonio', 'san-antonio', '300 Convent Street, Suite 1200, San Antonio, TX 78205' ), 'es' => array( 'San Antonio', 'oficina-san-antonio', '300 Convent Street, Suite 1200, San Antonio, TX 78205' ) ),
			),
		),
		array(
			'en' => array( 'Louisiana', 'louisiana', 'Our New Orleans office serves clients throughout Louisiana and the Gulf Coast.' ),
			'es' => array( 'Luisiana', 'oficinas-luisiana', 'Nuestra oficina de Nueva Orleans atiende a clientes en todo Luisiana y la costa del Golfo.' ),
			'cities' => array(
				array( 'en' => array( 'New Orleans', 'new-orleans', '400 Poydras Street, Suite 2100, New Orleans, LA 70130' ), 'es' => array( 'Nueva Orleans', 'oficina-nueva-orleans', '400 Poydras Street, Suite 2100, New Orleans, LA 70130' ) ),
			),
		),
	);
	$city_text = array(
		'en' => array( 'Injury lawyers in %s', 'Our %1$s team has helped thousands of local families after car crashes, trucking accidents and workplace injuries. We know the local courts, the local hospitals and the local roads.', 'Visit our office', 'Free parking is available. We also meet clients at home or in the hospital.' ),
		'es' => array( 'Abogados de lesiones en %s', 'Nuestro equipo en %1$s ha ayudado a miles de familias locales después de accidentes de auto, de camiones y lesiones en el trabajo. Conocemos las cortes, los hospitales y las carreteras de la zona.', 'Visite nuestra oficina', 'Hay estacionamiento gratis. También visitamos a clientes en su casa o en el hospital.' ),
	);

	foreach ( $states as $s => $state ) {
		$state_ids = array();
		foreach ( array( 'en', 'es' ) as $lang ) {
			list( $title, $slug, $intro ) = $state[ $lang ];
			$state_ids[ $lang ] = mx_insert( 'location', $title, $slug, mx_p( $intro, 'mx-lead' ), $lang, array( 'menu_order' => $s, 'post_excerpt' => $intro ) );
		}
		mx_link( $state_ids['en'], $state_ids['es'] );

		foreach ( $state['cities'] as $c => $city ) {
			$city_ids = array();
			foreach ( array( 'en', 'es' ) as $lang ) {
				list( $title, $slug, $address ) = $city[ $lang ];
				$t       = $city_text[ $lang ];
				$content = mx_p( sprintf( $t[1], $title ), 'mx-lead' )
					. mx_h( $t[2] ) . mx_p( $address . '<br>' . MERIDIAN_PHONE ) . mx_p( $t[3] );
				$city_ids[ $lang ] = mx_insert( 'location', $title, $slug, $content, $lang, array( 'post_parent' => $state_ids[ $lang ], 'menu_order' => $c, 'post_excerpt' => $address ) );
			}
			mx_link( $city_ids['en'], $city_ids['es'] );
		}
	}

	/* ---------- Articles */
	$articles = array(
		array(
			'en' => array( 'What to Do in the First 24 Hours After a Car Accident', 'first-24-hours-after-car-accident', 'The steps you take right after a crash protect your health and your claim.',
				array( 'Call 911 and get checked by a doctor, even if you feel fine.', 'Take photos of the vehicles, the road and your injuries.', 'Get the other driver’s insurance and the names of witnesses.', 'Do not give a recorded statement to the other driver’s insurer.', 'Talk to a lawyer before you sign anything.' ) ),
			'es' => array( 'Qué hacer en las primeras 24 horas después de un accidente de auto', 'primeras-24-horas-accidente-de-auto', 'Los pasos que da justo después de un choque protegen su salud y su reclamo.',
				array( 'Llame al 911 y vaya al médico, aunque se sienta bien.', 'Tome fotos de los vehículos, la calle y sus lesiones.', 'Pida el seguro del otro conductor y los nombres de testigos.', 'No dé una declaración grabada a la aseguradora del otro conductor.', 'Hable con un abogado antes de firmar cualquier cosa.' ) ),
		),
		array(
			'en' => array( 'Texas Non-Subscriber Employers: Your Rights After a Work Injury', 'texas-non-subscriber-employers', 'Texas is the only state where employers can opt out of workers’ compensation. Here is what that means for you.',
				array( 'Ask your employer in writing whether they carry workers’ compensation.', 'If they do not, you may be able to sue them for negligence.', 'Report the injury to your supervisor as soon as possible.', 'Be careful with any company “injury benefit plan” paperwork.' ) ),
			'es' => array( 'Empleadores no suscritos en Texas: sus derechos tras una lesión laboral', 'empleadores-no-suscritos-texas', 'Texas es el único estado donde los empleadores pueden no tener compensación laboral. Esto es lo que significa para usted.',
				array( 'Pregunte por escrito a su empleador si tiene compensación laboral.', 'Si no la tiene, puede demandarlo por negligencia.', 'Reporte la lesión a su supervisor lo antes posible.', 'Tenga cuidado con los documentos del “plan de beneficios por lesión” de la empresa.' ) ),
		),
		array(
			'en' => array( 'How Long Do I Have to File an Injury Claim?', 'deadline-to-file-injury-claim', 'Every state sets a deadline, called the statute of limitations. Missing it can end your case.',
				array( 'In Texas, most injury claims must be filed within two years.', 'Louisiana deadlines are generally shorter, often one or two years depending on the date of injury.', 'Claims against government agencies can require notice within months.', 'The safest choice is to talk to a lawyer as early as possible.' ) ),
			'es' => array( '¿Cuánto tiempo tengo para presentar un reclamo por lesiones?', 'plazo-para-reclamo-por-lesiones', 'Cada estado fija un plazo llamado prescripción. Si lo deja pasar, puede perder su caso.',
				array( 'En Texas, la mayoría de los reclamos deben presentarse en dos años.', 'En Luisiana los plazos suelen ser más cortos, de uno o dos años según la fecha de la lesión.', 'Los reclamos contra agencias del gobierno pueden exigir aviso en pocos meses.', 'Lo más seguro es hablar con un abogado lo antes posible.' ) ),
		),
	);
	$art_cta = array( 'en' => 'Questions about your situation? Our team answers calls 24/7.', 'es' => '¿Tiene preguntas sobre su situación? Nuestro equipo contesta llamadas 24/7.' );

	foreach ( $articles as $i => $article ) {
		$ids = array();
		foreach ( array( 'en', 'es' ) as $lang ) {
			list( $title, $slug, $intro, $steps ) = $article[ $lang ];
			$content = mx_p( $intro, 'mx-lead' ) . mx_list( $steps, 'mx-checks' ) . mx_p( $art_cta[ $lang ] )
				. mx_buttons( array( array( $labels[ $lang ]['free'], $u[ $lang ]['contact'], 'gold' ) ) );
			$ids[ $lang ] = mx_insert( 'post', $title, $slug, $content, $lang, array( 'post_excerpt' => $intro, 'post_date' => gmdate( 'Y-m-d H:i:s', time() - ( $i + 1 ) * 6 * DAY_IN_SECONDS ) ) );
		}
		mx_link( $ids['en'], $ids['es'] );
	}

	/* ---------- Pages */
	$home = array(
		'en' => array(
			'eyebrow' => 'Texas &amp; Louisiana Injury Lawyers · Se habla español',
			'h1'      => 'Hurt in an accident? We fight so you can heal.',
			'lead'    => 'Meridian Injury Law has helped thousands of families recover after car crashes, trucking accidents and workplace injuries. Free consultation, 24/7. No fee unless we win.',
			'stats'   => array( '<strong>$250M+</strong> recovered for clients', '<strong>4,000+</strong> families helped', '<strong>No fee</strong> unless we win', '<strong>24/7</strong> in English &amp; Spanish' ),
			'pa_h'    => 'How we can help', 'pa_p' => 'Choose your type of case to see how we handle it.',
			'why_h'   => 'Why families choose Meridian',
			'why'     => array( '<strong>Former insurance defense lawyers.</strong> We know their playbook, so we know how to beat it.', '<strong>Bilingual from day one.</strong> Every client can work with us fully in Spanish.', '<strong>We come to you.</strong> Home and hospital visits at no cost.', '<strong>You always know where your case stands.</strong> Updates every two weeks, guaranteed.' ),
			'loc_h'   => 'Offices near you', 'att_h' => 'Meet your legal team', 'art_h' => 'Helpful guides',
		),
		'es' => array(
			'eyebrow' => 'Abogados de lesiones en Texas y Luisiana · Hablamos español',
			'h1'      => '¿Se lesionó en un accidente? Nosotros luchamos para que usted sane.',
			'lead'    => 'Meridian Injury Law ha ayudado a miles de familias a recuperarse después de accidentes de auto, de camiones y lesiones en el trabajo. Consulta gratis, 24/7. No cobramos a menos que ganemos.',
			'stats'   => array( '<strong>$250M+</strong> recuperados para clientes', '<strong>4,000+</strong> familias ayudadas', '<strong>Sin honorarios</strong> a menos que ganemos', '<strong>24/7</strong> en español e inglés' ),
			'pa_h'    => 'Cómo podemos ayudarle', 'pa_p' => 'Elija su tipo de caso para ver cómo lo atendemos.',
			'why_h'   => 'Por qué las familias eligen Meridian',
			'why'     => array( '<strong>Antes defendíamos a aseguradoras.</strong> Conocemos sus tácticas y sabemos cómo vencerlas.', '<strong>Bilingües desde el primer día.</strong> Todo su caso puede llevarse en español.', '<strong>Vamos a usted.</strong> Visitas a domicilio y al hospital sin costo.', '<strong>Siempre sabe cómo va su caso.</strong> Actualizaciones cada dos semanas, garantizado.' ),
			'loc_h'   => 'Oficinas cerca de usted', 'att_h' => 'Conozca a su equipo legal', 'art_h' => 'Guías útiles',
		),
	);

	$pages = array();
	foreach ( array( 'en', 'es' ) as $lang ) {
		$h       = $home[ $lang ];
		$content = mx_section(
			mx_p( $h['eyebrow'], 'mx-eyebrow' )
			. "<!-- wp:heading {\"level\":1,\"className\":\"mx-hero__title\"} -->\n<h1 class=\"wp-block-heading mx-hero__title\">{$h['h1']}</h1>\n<!-- /wp:heading -->\n\n"
			. mx_p( $h['lead'], 'mx-lead' )
			. mx_buttons( array( array( $labels[ $lang ]['free'], $u[ $lang ]['contact'], 'gold' ), array( ( 'en' === $lang ? 'Call ' : 'Llame al ' ) . MERIDIAN_PHONE, 'tel:+17135550142', 'outline-light' ) ) ),
			'mx-hero'
		)
		. mx_section( mx_list( $h['stats'], 'mx-stats' ), 'mx-stats-band' )
		. mx_section( mx_h( $h['pa_h'] ) . mx_p( $h['pa_p'] ) . mx_sc( '[meridian_cards type="practice_area"]' ), 'mx-section' )
		. mx_section( mx_h( $h['why_h'] ) . mx_list( $h['why'], 'mx-features' ), 'mx-section mx-section--cream' )
		. mx_section( mx_h( $h['loc_h'] ) . mx_sc( '[meridian_cards type="location"]' ), 'mx-section' )
		. mx_section( mx_h( $h['att_h'] ) . mx_sc( '[meridian_cards type="attorney" limit="3"]' ), 'mx-section mx-section--cream' )
		. mx_section( mx_h( $h['art_h'] ) . mx_sc( '[meridian_cards type="post" limit="3"]' ), 'mx-section' )
		. mx_section( mx_sc( '[meridian_consult_form]' ), 'mx-section mx-section--navy' );

		$pages['home'][ $lang ] = mx_insert( 'page', 'en' === $lang ? 'Home' : 'Inicio', 'en' === $lang ? 'home' : 'inicio', $content, $lang );
	}

	$simple = array(
		'about'    => array(
			'en' => array( 'About Us', 'about', mx_p( 'Meridian Injury Law was founded in Houston with one goal: give injured people the same quality of representation that insurance companies buy for themselves.', 'mx-lead' ) . mx_h( 'Our promise' ) . mx_list( array( 'You will never pay a fee unless we win your case.', 'You can always reach a real person, day or night.', 'We explain every decision in plain English or Spanish.' ), 'mx-checks' ) . mx_buttons( array( array( 'Meet our attorneys', home_url( '/attorneys/' ), 'navy' ) ) ) ),
			'es' => array( 'Nosotros', 'nosotros', mx_p( 'Meridian Injury Law nació en Houston con una meta: dar a las personas lesionadas la misma calidad de representación que las aseguradoras compran para sí mismas.', 'mx-lead' ) . mx_h( 'Nuestra promesa' ) . mx_list( array( 'Nunca pagará honorarios a menos que ganemos su caso.', 'Siempre podrá hablar con una persona real, de día o de noche.', 'Le explicamos cada decisión en español o inglés, con palabras claras.' ), 'mx-checks' ) . mx_buttons( array( array( 'Conozca a nuestros abogados', home_url( "$es/attorneys/" ), 'navy' ) ) ) ),
		),
		'contact'  => array(
			'en' => array( 'Contact', 'contact', mx_p( 'Call <a href="tel:+17135550142">' . MERIDIAN_PHONE . '</a> any time, or send the form below. Consultations are free and confidential.', 'mx-lead' ) . mx_sc( '[meridian_consult_form]' ) ),
			'es' => array( 'Contacto', 'contacto', mx_p( 'Llame al <a href="tel:+17135550142">' . MERIDIAN_PHONE . '</a> a cualquier hora o envíe el formulario. Las consultas son gratis y confidenciales.', 'mx-lead' ) . mx_sc( '[meridian_consult_form]' ) ),
		),
		'articles' => array(
			'en' => array( 'Articles', 'articles', '' ),
			'es' => array( 'Artículos', 'articulos', '' ),
		),
	);
	foreach ( $simple as $key => $versions ) {
		foreach ( array( 'en', 'es' ) as $lang ) {
			list( $title, $slug, $content ) = $versions[ $lang ];
			$pages[ $key ][ $lang ] = mx_insert( 'page', $title, $slug, $content, $lang );
		}
	}
	foreach ( $pages as $pair ) {
		mx_link( $pair['en'], $pair['es'] );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $pages['home']['en'] );
	update_option( 'page_for_posts', $pages['articles']['en'] );
	update_option( 'meridian_pages', array(
		'home'     => $pages['home']['en'],
		'about'    => $pages['about']['en'],
		'contact'  => $pages['contact']['en'],
		'articles' => $pages['articles']['en'],
	) );

	update_option( 'meridian_seeded', 1 );

	// Polylang caches each language's homepage; refresh it now that the homepage is set.
	if ( $pll && method_exists( PLL()->model, 'clean_languages_cache' ) ) {
		PLL()->model->clean_languages_cache();
	}
	// Rebuild URL rules on the next normal page load, when every plugin's rules are registered.
	update_option( 'meridian_flush_rewrite', 1 );
	return $pll ? 'seeded with Polylang' : 'seeded (English only, Polylang missing)';
}
