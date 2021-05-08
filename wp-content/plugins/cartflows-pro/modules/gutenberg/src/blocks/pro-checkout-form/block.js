
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, } = wp.element;
const { InspectorControls, ColorPalette, } = wp.blockEditor;
const { PanelBody,SelectControl,RangeControl,ToggleControl, TextControl } = wp.components;
const { select, withSelect } = wp.data;

const { __ } = wp.i18n;
import styling from "./styling"
import inline from "./inline-styling"
import "./editor.scss"
import TypographyControl from "../../components/typography"

// Import Web font loader for google fonts.
import WebfontLoader from "../../components/typography/fontloader"

const CheckoutProFilterBlocks =  createHigherOrderComponent( ( BlockEdit)  => {  

		return ( { ...props } ) => {

			let getBlockName = select('core/block-editor').getBlockName( props.clientId );
			
			const $style = document.createElement( "style" )
			$style.setAttribute( "id", "cf-checkout-form-pro-style-" + props.clientId.substr( 0, 8 ) )
			document.head.appendChild( $style )
			var element = document.getElementById( "cf-checkout-form-pro-style-" + props.clientId.substr( 0, 8 ) )
			
			if( null !== element && undefined !== element ) {
				element.innerHTML = styling( props )
			}
		
			const {
				attributes: {
					productBorderColor,
					productBorderRadius,
					productBorderWidth,
					productBorderStyle,
					productTextBgColor,
					productTextColor,
					productTitleTextColor,
					orderbumpBorderColor,
					orderbumpBorderRadius,
					orderbumpBorderWidth,
					orderbumpBorderStyle,
					orderbumpTextBgColor,
					orderbumpTextColor,
					orderbumpcontentTextColor,
					orderbumpcontentBgColor,
					showprecheckoutoffer,
					OverlayBackgroundColor,
					ModalBackgroundColor,
					TitleColor,
					SubtitleColor,
					DescriptionColor,
					layout,
					// obposition,
					orderBumpSkin,
					orderBumpCheckboxArrow,
					orderBumpCheckboxArrowAnimation,
					// orderBumpCheckboxLabel,
					// orderBumpHighlightText,
					// orderBumpCheckboxProductDescription,
					sectionposition,
					twoStepBgColor,
					twoStepTextColor,
					productOptionsSkin,
					productOptionsImages,
					productOptionsSectionTitleText,
					//PreSkipText,
					//PreOrderText,
					//PreProductTitleText,
					//preSubTitleText,
					//preTitleText,
					//PreProductDescText,
					inputSkins,
					enableNote,
					noteText,
					stepOneTitleText,
					stepOneSubTitleText,
					stepTwoTitleText,
					stepTwoSubTitleText,
					offerButtonTitleText,
					offerButtonSubTitleText,
					tstextFontFamily,
					tstextFontWeight,
					tstextFontSubset,
					tstextFontSize,
					tstextFontSizeType,
					tstextFontSizeMobile,
					tstextFontSizeTablet,
					tstextLineHeight,
					tstextLineHeightType,
					tstextLineHeightMobile,
					tstextLineHeightTablet,
					tstextLoadGoogleFonts,
				},   
				setAttributes                                
				} = props

				let loadTextGoogleFonts;

				if( true === tstextLoadGoogleFonts ) {
					const tconfig = {
						google: {
							families: [ tstextFontFamily + ( tstextFontWeight ? ':' + tstextFontWeight : '' ) ],
						},
					};
					loadTextGoogleFonts = (
						<WebfontLoader config={ tconfig }>
						</WebfontLoader>
					)
				}
				
				props.setAttributes( { block_id: props.clientId.substr( 0, 8 ) } )
				const $pre_style = document.createElement( "style" )
				$pre_style.setAttribute( "id", "cf-pre-checkout-offer-style-" + props.clientId.substr( 0, 8 ) )
				document.head.appendChild( $pre_style )
				var element = document.getElementById( "cf-pre-checkout-offer-style-" + props.clientId.substr( 0, 8 ) )
					
				if( null !== element && undefined !== element ) {
					element.innerHTML = inline( props )
				}

				var pre_checkout_offer_wrapper = document.querySelector('.wcf-pre-checkout-offer-wrapper');
				if( true === showprecheckoutoffer ){
					if (  null !== pre_checkout_offer_wrapper && undefined !== pre_checkout_offer_wrapper ) {
						pre_checkout_offer_wrapper.setAttribute("style", "visibility: visible;opacity: 1;text-align: center;position: absolute;width: 80%;height: 60%;left: 0;top: 0;padding: 30px;z-index: 1042;background:unset;overflow-x: unset;overflow-y: unset;");

						//var preTitleTextEditor = preTitleText !== '' ? preTitleText : '{first_name}, Wait! Your Order Is Almost Complete...';
						//var preSubTitleTextEditor = preSubTitleText !== '' ? preSubTitleText : 'We have a special one time offer just for you.';
						//var PreProductTitleTextEditor = PreProductTitleText !== '' ? PreProductTitleText : 'Product Title';
						//var PreProductDescTextEditor = PreProductDescText !== '' ? PreProductDescText : 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as one time offer and make it irresistible.';
						//var PreOrderTextEditor = PreOrderText !== '' ? PreOrderText : 'Yes, Add to My Order!';
						//var PreSkipTextEditor = PreSkipText !== '' ? PreSkipText : 'No, thanks!';

						//pre_checkout_offer_wrapper.querySelector('.wcf-content-modal-title h1').innerHTML = preTitleTextEditor;
						//pre_checkout_offer_wrapper.querySelector('.wcf-content-modal-sub-title span').innerHTML = preSubTitleTextEditor;
						//pre_checkout_offer_wrapper.querySelector('.wcf-pre-checkout-offer-product-title h1').innerHTML = PreProductTitleTextEditor;
						//pre_checkout_offer_wrapper.querySelector('.wcf-pre-checkout-offer-desc span').innerHTML = PreProductDescTextEditor;
						//pre_checkout_offer_wrapper.querySelector('.wcf-pre-checkout-offer-btn-action.wcf-pre-checkout-add-cart-btn button').innerHTML = PreOrderTextEditor;
						//pre_checkout_offer_wrapper.querySelector('.wcf-pre-checkout-offer-btn-action.wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip').innerHTML = PreSkipTextEditor;

					}
				} else{
					if ( null !== pre_checkout_offer_wrapper && undefined !== pre_checkout_offer_wrapper ) {
						pre_checkout_offer_wrapper.setAttribute("style", "display: none;visibility: hidden;opacity: 0;text-align: center;position: absolute;width: 80%;height: 60%;left: 0;top: 0;padding: 30px;z-index: 1042;background:unset;overflow-x: unset;overflow-y: unset;");
					}
				}

			const input_field_skins = () => {
				if( "wcfb/checkout-form" === getBlockName ){
				return (
					<PanelBody title={ __( "Input Field", 'cartflows-pro' ) } initialOpen={ false }>

						<SelectControl
							label={ __( "Style", 'cartflows-pro' ) }
							value={ inputSkins }
							onChange={ ( value ) => setAttributes( { inputSkins: value } ) }
							options={ [
								{ value: "deafult", label: __( "Default", 'cartflows-pro' ) },
								{ value: "style-one", label: __( "Floating Labels", 'cartflows-pro' ) },
							] }
						/>

					</PanelBody>
				)
			}}

			const product_settings = () => {
				if( 'yes' === cfp_blocks_info.enable_product_options && "wcfb/checkout-form" === getBlockName ){		
				return (
					<PanelBody title={ __( "Product Option", 'cartflows-pro' ) } initialOpen={ false }>
						<SelectControl
							label={ __( "Position", 'cartflows-pro' ) }
							value={ sectionposition }
							onChange={ ( value ) => setAttributes( { sectionposition: value } ) }
							options={ [
								{ value: "before-customer", label: __( "Before Checkout Section", 'cartflows-pro' ) },
								{ value: "after-customer", label: __( "After Customer Details", 'cartflows-pro' ) },
								{ value: "before-order", label: __( "Before Order Review", 'cartflows-pro' ) },
							] }
						/>
						<SelectControl
							label={ __( "Skin", 'cartflows-pro' ) }
							value={ productOptionsSkin }
							onChange={ ( value ) => setAttributes( { productOptionsSkin: value } ) }
							options={ [
								{ value: "classic", label: __( "Classic", 'cartflows-pro' ) },
								{ value: "cards", label: __( "Cards", 'cartflows-pro' ) },
							] }
						/>
						<SelectControl
							label={ __( "Show Product Images", 'cartflows-pro' ) }
							value={ productOptionsImages }
							onChange={ ( value ) => setAttributes( { productOptionsImages: value } ) }
							options={ [
								{ value: "yes", label: __( "Yes", 'cartflows-pro' ) },
								{ value: "no", label: __( "No", 'cartflows-pro' ) },
							] }
						/>
						<p className="components-base-control__label">{__( "Section Title Text", 'cartflows-pro' )}</p>
						<TextControl
							value={ productOptionsSectionTitleText }
							onChange={ ( value ) => setAttributes( { productOptionsSectionTitleText: value } ) }
							placeholder={__( "Your Proucts", 'cartflows-pro' )}
						/>
						<hr className="cfp-editor__separator" />
						<p className="cfp-setting-label">{ __( "Title Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: productTitleTextColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ productTitleTextColor }
								onChange={ ( colorValue ) => setAttributes( { productTitleTextColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: productTextColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ productTextColor }
								onChange={ ( colorValue ) => setAttributes( { productTextColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Background Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: productTextBgColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ productTextBgColor }
								onChange={ ( colorValue ) => setAttributes( { productTextBgColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<SelectControl
								label={ __( "Border Style", 'cartflows-pro' ) }
								value={ productBorderStyle }
								onChange={ ( value ) => setAttributes( { productBorderStyle: value } ) }
								options={ [
									{ value: "none", label: __( "None", 'cartflows-pro' ) },
									{ value: "solid", label: __( "Solid", 'cartflows-pro' ) },
									{ value: "dotted", label: __( "Dotted", 'cartflows-pro' ) },
									{ value: "dashed", label: __( "Dashed", 'cartflows-pro' ) },
									{ value: "double", label: __( "Double", 'cartflows-pro' ) },
									{ value: "groove", label: __( "Groove", 'cartflows-pro' ) },
									{ value: "inset", label: __( "Inset", 'cartflows-pro' ) },
									{ value: "outset", label: __( "Outset", 'cartflows-pro' ) },
									{ value: "ridge", label: __( "Ridge", 'cartflows-pro' ) },
								] }
							/>
							{ "none" != productBorderStyle && (
								<RangeControl
									label={ __( "Border Width (px)", 'cartflows-pro' ) }
									value={ productBorderWidth }
									onChange={ ( value ) => setAttributes( { productBorderWidth: value } ) }
									min={ 0 }
									max={ 50 }
									allowReset
								/>
							) }
							<RangeControl
								label={ __( "Border Radius", 'cartflows-pro' ) }
								value={ productBorderRadius }
								onChange={ ( value ) => setAttributes( { productBorderRadius: value } ) }
								min={ 0 }
								max={ 100 }
								allowReset
							/>
							{ "none" != productBorderStyle && (
								<Fragment>
									<p className="cfp-setting-label">{ __( "Border Color", 'cartflows-pro' ) }
										<span className="components-base-control__label">
											<span className="component-color-indicator" style={{ backgroundColor: productBorderColor }} ></span></span></p>
									<ColorPalette
										value={ productBorderColor }
										onChange={ ( colorValue ) => setAttributes( { productBorderColor: colorValue } ) }
										allowReset
									/>
								</Fragment>
							) }
					</PanelBody>
				)
				}
				if( 'no' === cfp_blocks_info.enable_product_options && "wcfb/checkout-form" === getBlockName ){
					return (
						<PanelBody title={ __( "Product option", 'cartflows-pro' ) } initialOpen={ false }>
							<p className="cf-settings-notice">{ __( "Please enable Product option from meta settings.", 'cartflows-pro' ) }</p>
						</PanelBody>
					)
				}
			}
	
			const orderbump_settings = () => {	
				if( 'yes' === cfp_blocks_info.enable_order_bump && "wcfb/checkout-form" === getBlockName ){
				return (
						<PanelBody title={ __( "Order Bump", 'cartflows-pro' ) } initialOpen={ false }>
							{/* <SelectControl
								label={ __( "Order Bump Position", 'cartflows-pro' ) }
								value={ obposition }
								onChange={ ( value ) => setAttributes( { obposition: value } ) }
								options={ [
									{ value: "before-checkout", label: __( "Before Checkout", 'cartflows-pro' ) },
									{ value: "after-customer", label: __( "After Customer Details", 'cartflows-pro' ) },
									{ value: "after-order", label: __( "After Order", 'cartflows-pro' ) },
									{ value: "after-payment", label: __( "After Payment", 'cartflows-pro' ) },
								] }
							/> */}
							<SelectControl
								label={ __( "Order Bump Skin", 'cartflows-pro' ) }
								value={ orderBumpSkin }
								onChange={ ( value ) => setAttributes( { orderBumpSkin: value } ) }
								options={ [
									{ value: "style-1", label: __( "Style 1", 'cartflows-pro' ) },
									{ value: "style-2", label: __( "Style 2", 'cartflows-pro' ) },
								] }
							/>
							<SelectControl
								label={ __( "Enable Arrow", 'cartflows-pro' ) }
								value={ orderBumpCheckboxArrow }
								onChange={ ( value ) => setAttributes( { orderBumpCheckboxArrow: value } ) }
								options={ [
									{ value: "yes", label: __( "On", 'cartflows-pro' ) },
									{ value: "no", label: __( "Off", 'cartflows-pro' ) },
								] }
							/>
							<SelectControl
								label={ __( "Enable Arrow Animation", 'cartflows-pro' ) }
								value={ orderBumpCheckboxArrowAnimation }
								onChange={ ( value ) => setAttributes( { orderBumpCheckboxArrowAnimation: value } ) }
								options={ [
									{ value: "yes", label: __( "On", 'cartflows-pro' ) },
									{ value: "no", label: __( "Off", 'cartflows-pro' ) },
								] }
							/>
							{/* <p className="components-base-control__label">{__( "Checkbox Label", 'cartflows-pro' )}</p>
							<TextControl
							value={ orderBumpCheckboxLabel }
							onChange={ ( value ) => setAttributes( { orderBumpCheckboxLabel: value } ) }
							placeholder={__( "Yes, I will take it!", 'cartflows-pro' )}
							/> */}
							{/* <p className="components-base-control__label">{__( "Highlight Text", 'cartflows-pro' )}</p>
							<TextControl
							value={ orderBumpHighlightText }
							onChange={ ( value ) => setAttributes( { orderBumpHighlightText: value } ) }
							placeholder={__( "ONE TIME OFFER", 'cartflows-pro' )}
							/> */}
							{/* <p className="components-base-control__label">{__( "Product Description", 'cartflows-pro' )}</p>
							<TextControl
							value={ orderBumpCheckboxProductDescription }
							onChange={ ( value ) => setAttributes( { orderBumpCheckboxProductDescription: value } ) }
							placeholder={__( "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut, quod hic expedita consectetur vitae nulla sint adipisci cupiditate at. Commodi, dolore hic eaque tempora a repudiandae obcaecati deleniti mollitia possimus.", 'cartflows-pro' )}
							/> */}
							<hr className="cf-editor__separator" />
							<p className="cfp-setting-label">{ __( "Text Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: orderbumpTextColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ orderbumpTextColor }
								onChange={ ( colorValue ) => setAttributes( { orderbumpTextColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Text Background Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: orderbumpTextBgColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ orderbumpTextBgColor }
								onChange={ ( colorValue ) => setAttributes( { orderbumpTextBgColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Description Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: orderbumpcontentTextColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ orderbumpcontentTextColor }
								onChange={ ( colorValue ) => setAttributes( { orderbumpcontentTextColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Description Background Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: orderbumpcontentBgColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ orderbumpcontentBgColor }
								onChange={ ( colorValue ) => setAttributes( { orderbumpcontentBgColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<SelectControl
								label={ __( "Border Style", 'cartflows-pro' ) }
								value={ orderbumpBorderStyle }
								onChange={ ( value ) => setAttributes( { orderbumpBorderStyle: value } ) }
								options={ [
									{ value: "none", label: __( "None", 'cartflows-pro' ) },
									{ value: "solid", label: __( "Solid", 'cartflows-pro' ) },
									{ value: "dotted", label: __( "Dotted", 'cartflows-pro' ) },
									{ value: "dashed", label: __( "Dashed", 'cartflows-pro' ) },
									{ value: "double", label: __( "Double", 'cartflows-pro' ) },
									{ value: "groove", label: __( "Groove", 'cartflows-pro' ) },
									{ value: "inset", label: __( "Inset", 'cartflows-pro' ) },
									{ value: "outset", label: __( "Outset", 'cartflows-pro' ) },
									{ value: "ridge", label: __( "Ridge", 'cartflows-pro' ) },
								] }
							/>
							{ "none" != orderbumpBorderStyle && (
								<RangeControl
									label={ __( "Border Width (px)", 'cartflows-pro' ) }
									value={ orderbumpBorderWidth }
									onChange={ ( value ) => setAttributes( { orderbumpBorderWidth: value } ) }
									min={ 0 }
									max={ 50 }
									allowReset
								/>
							) }
							<RangeControl
								label={ __( "Border Radius", 'cartflows-pro' ) }
								value={ orderbumpBorderRadius }
								onChange={ ( value ) => setAttributes( { orderbumpBorderRadius: value } ) }
								min={ 0 }
								max={ 100 }
								allowReset
							/>
							{ "none" != orderbumpBorderStyle && (
							<Fragment>
								<p className="cfp-setting-label">{ __( "Border Color", 'cartflows-pro' ) }
									<span className="components-base-control__label">
										<span className="component-color-indicator" style={{ backgroundColor: orderbumpBorderColor }} ></span></span></p>
								<ColorPalette
									value={ orderbumpBorderColor }
									onChange={ ( colorValue ) => setAttributes( { orderbumpBorderColor: colorValue } ) }
									allowReset
								/>
							</Fragment>
							) }
					</PanelBody>
				)
				}
				if( 'no' === cfp_blocks_info.enable_order_bump && "wcfb/checkout-form" === getBlockName ){
					return (
						<PanelBody title={ __( "Order Bump", 'cartflows-pro' ) } initialOpen={ false }>
							<p className="cf-settings-notice">{ __( "Please enable Order Bump from meta settings.", 'cartflows-pro' ) }</p>
						</PanelBody>
					)
				}
			}
	
			const pre_checkout_settings = () => {	
				if( 'yes' === cfp_blocks_info.enable_checkout_offer && "wcfb/checkout-form" === getBlockName ){
				return (
					<PanelBody title={ __( "Pre Checkout Offer", 'cartflows-pro' ) } initialOpen={ false }>
						<p className="components-base-control__label">{__( "Title Text", 'cartflows-pro' )}</p>
						<ToggleControl
						label={ __( "Enable preview", 'cartflows-pro' ) }
						checked={ showprecheckoutoffer }
						onChange={ ( value ) => setAttributes( { showprecheckoutoffer: ! showprecheckoutoffer  } ) }
						/>
						{/*<TextControl
							value={ preTitleText }
							onChange={ ( value ) => setAttributes( { preTitleText: value } ) }
							placeholder={__( "{first_name}, Wait! Your Order Is Almost Complete...", 'cartflows-pro' )}
						/>
						<p className="components-base-control__label">{__( "Sub-Title Text", 'cartflows-pro' )}</p>
						<TextControl
							value={ preSubTitleText }
							onChange={ ( value ) => setAttributes( { preSubTitleText: value } ) }
							placeholder={__( "We have a special one time offer just for you.", 'cartflows-pro' )}
						/>
						<p className="components-base-control__label">{__( "Product Title Text", 'cartflows-pro' )}</p>
						<TextControl
							value={ PreProductTitleText }
							onChange={ ( value ) => setAttributes( { PreProductTitleText: value } ) }
							placeholder={__( "Product Title", 'cartflows-pro' )}
						/>
						<p className="components-base-control__label">{__( "Product Description Text", 'cartflows-pro' )}</p>
						<TextControl
							value={ PreProductDescText }
							onChange={ ( value ) => setAttributes( { PreProductDescText: value } ) }
							placeholder={__( "Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as one time offer and make it irresistible.", 'cartflows-pro' )}
						/>
						<p className="components-base-control__label">{__( "Order Button Text", 'cartflows-pro' )}</p>
						<TextControl
							value={ PreOrderText }
							onChange={ ( value ) => setAttributes( { PreOrderText: value } ) }
							placeholder={__( "Yes, Add to My Order!", 'cartflows-pro' )}
						/>
						<p className="components-base-control__label">{__( "Skip Button Text", 'cartflows-pro' )}</p>
						<TextControl
							value={ PreSkipText } 
							onChange={ ( value ) => setAttributes( { PreSkipText: value } ) }
							placeholder={__( "No, thanks!", 'cartflows-pro' )}
						/>*/}
						<hr className="cf-editor__separator" />
						<p className="cf-setting-label">{ __( "Title Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: TitleColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ TitleColor }
								onChange={ ( colorValue ) => setAttributes( { TitleColor: colorValue } ) }
								allowReset
							/>
							<hr className="cf-editor__separator" />
							<p className="cf-setting-label">{ __( "Subtitle Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: SubtitleColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ SubtitleColor }
								onChange={ ( colorValue ) => setAttributes( { SubtitleColor: colorValue } ) }
								allowReset
							/>
							<hr className="cf-editor__separator" />
							<p className="cf-setting-label">{ __( "Description Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: DescriptionColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ DescriptionColor }
								onChange={ ( colorValue ) => setAttributes( { DescriptionColor: colorValue } ) }
								allowReset
							/>
							<hr className="cf-editor__separator" />
							<p className="cf-settings-notice">{ __( "Overlay color visible at front-end.", 'cartflows-pro' ) }</p>
							<p className="cf-setting-label">{ __( "Overlay Background Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: OverlayBackgroundColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ OverlayBackgroundColor }
								onChange={ ( colorValue ) => setAttributes( { OverlayBackgroundColor: colorValue } ) }
								allowReset
							/>
							<hr className="cf-editor__separator" />
							<p className="cf-setting-label">{ __( "Modal Background Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: ModalBackgroundColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ ModalBackgroundColor }
								onChange={ ( colorValue ) => setAttributes( { ModalBackgroundColor: colorValue } ) }
								allowReset
							/>
					</PanelBody>
					)
				} 
				if( 'no' === cfp_blocks_info.enable_checkout_offer && "wcfb/checkout-form" === getBlockName ){
					return (
						<PanelBody title={ __( "Pre Checkout Offer", 'cartflows-pro' ) } initialOpen={ false }>
							<p className="cf-settings-notice">{ __( "Please enable Pre Checkout Offer from meta settings.", 'cartflows-pro' ) }</p>
						</PanelBody>
					)
				}
			}

			const checkout_form_layout = () => {	
				if( "wcfb/checkout-form" === getBlockName ){
				return (
					<PanelBody title={ __( "Layout", 'cartflows-pro' ) } initialOpen={ false }>
						<SelectControl
							label={ __( "Style", 'cartflows-pro' ) }
							value={ layout }
							onChange={ ( value ) => setAttributes( { layout: value } ) }
							options={ [
								{ value: "one-column", label: __( "One Column", 'cartflows-pro' ) },
								{ value: "two-column", label: __( "Two Column", 'cartflows-pro' ) },
								{ value: "two-step", label: __( "Two Step", 'cartflows-pro' ) },
							] }
						/>
					</PanelBody>
				)
			}}


			const twostep_settings = () => {
				if( 'two-step' === layout ){	
				return (
						<PanelBody title={ __( "Two Step", 'cartflows-pro' ) } initialOpen={ false }>
							<SelectControl
								label={ __( "Enable Checkout Note", 'cartflows-pro' ) }
								value={ enableNote }
								onChange={ ( value ) => setAttributes( { enableNote: value } ) }
								options={ [
									{ value: "yes", label: __( "Yes", 'cartflows-pro' ) },
									{ value: "no", label: __( "No", 'cartflows-pro' ) },
								] }
							/>
							<p className="components-base-control__label">{__( "Note Text", 'cartflows-pro' )}</p>
							<TextControl
								value={ noteText }
								onChange={ ( value ) => setAttributes( { noteText: value } ) }
								placeholder={__( "Get Your FREE copy of CartFlows in just few steps.", 'cartflows-pro' )}
							/>
							<hr className="cfp-editor__separator" />
							<h2>{ __( "Steps", 'cartflows-pro' ) }</h2>
							<p className="components-base-control__label">{__( "Step One Title", 'cartflows-pro' )}</p>
							<TextControl
								value={ stepOneTitleText }
								onChange={ ( value ) => setAttributes( { stepOneTitleText: value } ) }
								placeholder={__( "Shipping", 'cartflows-pro' )}
							/>
							<p className="components-base-control__label">{__( "Step One Sub Title", 'cartflows-pro' )}</p>
							<TextControl
								value={ stepOneSubTitleText }
								onChange={ ( value ) => setAttributes( { stepOneSubTitleText: value } ) }
								placeholder={__( "Where to ship it?", 'cartflows-pro' )}
							/>
							<p className="components-base-control__label">{__( "Step Two Title", 'cartflows-pro' )}</p>
							<TextControl
								value={ stepTwoTitleText }
								onChange={ ( value ) => setAttributes( { stepTwoTitleText: value } ) }
								placeholder={__( "Payment", 'cartflows-pro' )}
							/>
							<p className="components-base-control__label">{__( "Step Two Sub Title", 'cartflows-pro' )}</p>
							<TextControl
								value={ stepTwoSubTitleText }
								onChange={ ( value ) => setAttributes( { stepTwoSubTitleText: value } ) }
								placeholder={__( "Of your order", 'cartflows-pro' )}
							/>
							<hr className="cfp-editor__separator" />
							<h2>{ __( "Offer Button", 'cartflows-pro' ) }</h2>
							<p className="components-base-control__label">{__( "Offer Button Title", 'cartflows-pro' )}</p>
							<TextControl
								value={ offerButtonTitleText }
								onChange={ ( value ) => setAttributes( { offerButtonTitleText: value } ) }
								placeholder={__( "For Special Offer Click Here", 'cartflows-pro' )}
							/>
							<p className="components-base-control__label">{__( "Offer Button Sub Title", 'cartflows-pro' )}</p>
							<TextControl
								value={ offerButtonSubTitleText }
								onChange={ ( value ) => setAttributes( { offerButtonSubTitleText: value } ) }
								placeholder={__( "Yes! I want this offer!", 'cartflows-pro' )}
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Text Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: twoStepTextColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ twoStepTextColor }
								onChange={ ( colorValue ) => setAttributes( { twoStepTextColor: colorValue } ) }
								allowReset
							/>
							<hr className="cfp-editor__separator" />
							<p className="cfp-setting-label">{ __( "Background Color", 'cartflows-pro' ) }
								<span className="components-base-control__label">
									<span className="component-color-indicator" style={{ backgroundColor: twoStepBgColor }} ></span>
								</span>
							</p>
							<ColorPalette
								value={ twoStepBgColor }
								onChange={ ( colorValue ) => setAttributes( { twoStepBgColor: colorValue } ) }
								allowReset
							/>
							<TypographyControl
								label={ __( "Typography", 'cartflows-pro' ) }
								attributes = { props }
								setAttributes = { setAttributes }
								loadGoogleFonts = { { value: tstextLoadGoogleFonts, label:'tstextLoadGoogleFonts' } }
								fontFamily = { { value: tstextFontFamily, label:'tstextFontFamily' } }
								fontWeight = { { value: tstextFontWeight, label:'tstextFontWeight' } }
								fontSubset = { { value: tstextFontSubset, label:'tstextFontSubset' } }
								fontSizeType = { { value: tstextFontSizeType, label:'tstextFontSizeType'  } }
								fontSize = { { value: tstextFontSize, label:'tstextFontSize' } }
								fontSizeMobile = { { value: tstextFontSizeMobile, label:'tstextFontSizeMobile' } }
								fontSizeTablet= { { value: tstextFontSizeTablet, label:'tstextFontSizeTablet' } }
								lineHeightType = { { value: tstextLineHeightType, label:'tstextLineHeightType'  } }
								lineHeight = { { value: tstextLineHeight, label:'tstextLineHeight' } }
								lineHeightMobile = { { value: tstextLineHeightMobile, label:'tstextLineHeightMobile' } }
								lineHeightTablet= { { value: tstextLineHeightTablet, label:'tstextLineHeightTablet' } }
							/>
					</PanelBody>
				)
				}
			}

			return (			
				<Fragment>
					<BlockEdit { ...props } />
					<InspectorControls>
					{/* {checkout_form_layout()} */}
					{twostep_settings()}
				   	{product_settings()}
				   	{orderbump_settings()}
				   	{pre_checkout_settings()}
					{/* {input_field_skins()} */}
					</InspectorControls>
					{ loadTextGoogleFonts }
				</Fragment>
			);
	
		}
}, "CheckoutProFilterBlocks" );

if( 'checkout' === cfp_blocks_info.step_type && cfp_blocks_info.is_woo_active){
	addFilter(
		'editor.BlockEdit',
		'wcfp/cfp-filter-blocks',
		CheckoutProFilterBlocks
	);
}
