/**
 * Registers a "Mededelingen" variation of the core Query Loop block,
 * preconfigured to list the newest mededelingen.
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

const VARIATION_NAME = 'soli-mededelingen/mededelingen-loop';
const POST_TYPE = 'soli_mededeling';

registerBlockVariation( 'core/query', {
	name: VARIATION_NAME,
	title: __( 'Mededelingen', 'soli-mededelingen' ),
	description: __(
		'Displays the latest mededelingen (members-only announcements).',
		'soli-mededelingen'
	),
	icon: 'megaphone',
	attributes: {
		namespace: VARIATION_NAME,
		query: {
			postType: POST_TYPE,
			perPage: 5,
			offset: 0,
			order: 'desc',
			orderBy: 'date',
			inherit: false,
		},
	},
	innerBlocks: [
		[
			'core/post-template',
			{},
			[
				[ 'core/post-title', { isLink: true } ],
				[ 'core/post-date' ],
				[ 'core/post-excerpt' ],
			],
		],
		[ 'core/query-no-results' ],
	],
	scope: [ 'inserter', 'block' ],
	isActive: ( blockAttributes ) =>
		blockAttributes.namespace === VARIATION_NAME ||
		blockAttributes.query?.postType === POST_TYPE,
} );
