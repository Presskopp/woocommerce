/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { getAdminLink } from '@woocommerce/settings';
import interpolateComponents from '@automattic/interpolate-components';
import { Link, useFormContext } from '@woocommerce/components';
import { PartialProduct, Product, ProductVariation } from '@woocommerce/data';
import { recordEvent } from '@woocommerce/tracks';

/**
 * Internal dependencies
 */
import { getAdminSetting } from '~/utils/admin-settings';

export const ManageStockSection: React.FC< ManageStockSectionProps > = ( {
	parent,
} ) => {
	const { getInputProps, setValue } = useFormContext<
		Product | ProductVariation
	>();
	const notifyLowStockAmount = getAdminSetting( 'notifyLowStockAmount', 2 );
	const stockQuantityProps = getInputProps< number >( 'stock_quantity' );
	const lowStockAmountProps = getInputProps< number >( 'low_stock_amount' );

	return (
		<>
			<h4>{ __( 'Product quantity', 'woocommerce' ) }</h4>
			<TextControl
				{ ...stockQuantityProps }
				type="number"
				label={ __( 'Current quantity', 'woocommerce' ) }
				min={ 0 }
				value={ String( stockQuantityProps.value ?? '' ) }
				placeholder={
					parent?.stock_quantity
						? String( parent.stock_quantity )
						: undefined
				}
				onFocus={ () => {
					if (
						! Number.isInteger( stockQuantityProps.value ) &&
						Number.isInteger( parent?.stock_quantity )
					) {
						setValue( 'stock_quantity', parent?.stock_quantity );
					}
				} }
			/>
			<TextControl
				{ ...lowStockAmountProps }
				type="number"
				label={ __( 'Email me when quantity reaches', 'woocommerce' ) }
				value={ String( lowStockAmountProps.value ?? '' ) }
				placeholder={
					Number.isInteger( parent?.low_stock_amount )
						? String( parent?.low_stock_amount )
						: sprintf(
								// translators: Default quantity to notify merchants of low stock.
								__( '%d (store default)', 'woocommerce' ),
								notifyLowStockAmount
						  )
				}
				min={ 0 }
				onFocus={ () => {
					if (
						! Number.isInteger( lowStockAmountProps.value ) &&
						Number.isInteger( parent?.low_stock_amount )
					) {
						setValue(
							'low_stock_amount',
							parent?.low_stock_amount
						);
					}
				} }
			/>
			<span className="woocommerce-product-form__secondary-text">
				{ interpolateComponents( {
					mixedString: __(
						'Make sure to enable notifications in {{link}}store settings{{/link}}.',
						'woocommerce'
					),
					components: {
						link: (
							<Link
								href={ getAdminLink(
									'admin.php?page=wc-settings&tab=products&section=inventory'
								) }
								target="_blank"
								type="wp-admin"
								onClick={ () => {
									recordEvent(
										'product_pricing_list_price_help_tax_settings_click'
									);
								} }
							>
								<></>
							</Link>
						),
						strong: <strong />,
					},
				} ) }
			</span>
		</>
	);
};

export type ManageStockSectionProps = {
	parent?: PartialProduct;
};
