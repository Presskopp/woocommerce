/**
 * External dependencies
 */
import React from 'react';
import { Slot, Fill } from '@wordpress/components';
import { createElement } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { createOrderedChildren, sortFillsByOrder } from '../utils';

/**
 * Create a Fill for extensions to add items to the Product edit page.
 *
 * @slotFill WooProductFieldItem
 * @scope woocommerce-admin
 * @example
 * const MyProductDetailsFieldItem = () => (
 * <WooProductFieldItem fieldName="name" categoryName="Product details" location="after">My header item</WooProductFieldItem>
 * );
 *
 * registerPlugin( 'my-extension', {
 * render: MyProductDetailsFieldItem,
 * scope: 'woocommerce-admin',
 * } );
 * @param {Object}  param0
 * @param {Array}   param0.children  - Node children.
 * @param {number}  param0.order - Order of Fill component.
 * @param {string}  param0.section - Section name.
 */

type WooProductFieldItemProps = {
	id: string;
	section: string;
	pluginId: string;
	order?: number;
};

type WooProductFieldSlotProps = {
	section: string;
};

export const WooProductFieldItem: React.FC< WooProductFieldItemProps > & {
	Slot: React.FC< Slot.Props & WooProductFieldSlotProps >;
} = ( { children, order = 1, section } ) => {
	return (
		<Fill name={ `woocommerce_product_field_${ section }` }>
			{ ( fillProps: Fill.Props ) => {
				return createOrderedChildren( children, order, fillProps );
			} }
		</Fill>
	);
};

WooProductFieldItem.Slot = ( { fillProps, section } ) => {
	const fillName = `woocommerce_product_field_${ section }`;

	return (
		<Slot name={ fillName } fillProps={ fillProps }>
			{ ( fills ) => {
				const filteredFills = applyFilters(
					'woo_product_field_fills',
					fills,
					fillName
				) as JSX.Element[][];
				if ( sortFillsByOrder ) {
					return sortFillsByOrder( filteredFills );
				}
				return null;
			} }
		</Slot>
	);
};
