<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WPCRM_System_Export_Projects extends WPCRM_System_Export{
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.1
	 */
	public $export_type = 'wpcrm-project';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 2.1
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'project_name' 		=> __( 'Project Name', 'wp-crm-system-import-organizations' ),
			'description'		=> __( 'Description', 'wp-crm-system-import-organizations' ),
			'close_date' 		=> __( 'Close Date', 'wp-crm-system-import-organizations' ),
			'progress'			=> __( 'Progress', 'wp-crm-system-import-organizations' ),
			'status'			=> __( 'Status', 'wp-crm-system-import-organizations' ),
			'value'				=> __( 'Value', 'wp-crm-system-import-organizations' ),
			'assigned_to'		=> __( 'Assigned To', 'wp-crm-system-import-organizations' ),
			'organization'		=> __( 'Organization Name', 'wp-crm-system-import-organizations' ),
			'organization_id'	=> __( 'Organization ID', 'wp-crm-system-import-organizations' ),
			'contact'			=> __( 'Contact Name', 'wp-crm-system-import-organizations' ),
			'contact_id'		=> __( 'Contact ID', 'wp-crm-system-import-organizations' )
		);

		if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
			$field_count = get_option( '_wpcrm_system_custom_field_count' );
			if( $field_count ){
				$custom_fields = array();
				for( $field = 1; $field <= $field_count; $field++ ){
					// Make sure we want this field to be imported.
					$field_scope = get_option( '_wpcrm_custom_field_scope_' . $field );
					$can_export = $field_scope == $this->export_type ? true : false;
					if( $can_export ){
						$custom_fields[] = get_option( '_wpcrm_custom_field_name_' . $field );
					}
				}
				$cols = array_merge( $cols, $custom_fields );
			}
		}

		$cols = apply_filters( 'wpcrm_system_export_cols_' . $this->export_type, $cols );

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.0
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		$get_ids = $this->get_cpt_post_ids();
		foreach ( $get_ids as $id ){
			$data[$id] = array(
				'project_name' 		=> get_the_title( $id ),
				'description' 		=> esc_html( get_post_meta( $id, '_wpcrm_project-description', true ) ),
				'close_date'		=> date( get_option( 'wpcrm_system_php_date_format' ), get_post_meta( $id, '_wpcrm_project-closedate', true ) ),
				'progress'			=> get_post_meta( $id, '_wpcrm_project-progress', true ),
				'status'			=> get_post_meta( $id, '_wpcrm_project-status', true ),
				'value'				=> get_post_meta( $id, '_wpcrm_project-value', true ),
				'assigned_to'		=> get_post_meta( $id, '_wpcrm_project-assigned', true ),
				'organization'		=> get_the_title( get_post_meta( $id, '_wpcrm_project-organization', true ) ),
				'organization_id'	=> get_post_meta( $id, '_wpcrm_project-organization', true ),
				'contact'			=> get_the_title( get_post_meta( $id, '_wpcrm_project-contact', true ) ),
				'contact_id'		=> get_post_meta( $id, '_wpcrm_project-contact', true )
			);
			if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
				$field_count 	= get_option( '_wpcrm_system_custom_field_count' );
				if( $field_count ){
					for( $field = 1; $field <= $field_count; $field++ ){
						// Make sure we want this field to be imported.
						$field_scope 	= get_option( '_wpcrm_custom_field_scope_' . $field );
						$field_type		= get_option( '_wpcrm_custom_field_type_' . $field );
						$can_export 	= $field_scope == $this->export_type ? true : false;
						if( $can_export ){
							$value 	= get_post_meta( $id, '_wpcrm_custom_field_id_' . $field, true );
							switch ( $field_type ) {
								case 'datepicker':
									$export = date( get_option( 'wpcrm_system_php_date_format' ), $value );
									break;
								case 'repeater-date':
									if ( is_array( $value ) ){
										foreach ( $value as $key => $v ){
											$values[$key] = date( get_option( 'wpcrm_system_php_date_format' ), $v );
										}
										$export = implode( ',', $values );
									} else {
										$export = '';
									}
									break;
								case 'repeater-file':
								case 'repeater-text':
								case 'repeater-textarea':
									if ( is_array( $value ) ){
										$export = implode( ',', $value );
									} else {
										$export = '';
									}
									break;
								default:
									$export = $value;
									break;
							}
							$data[$id][] = $export;
						}
					}
				}
			}
		}

		$data = apply_filters( 'wpcrm_system_export_get_data', $data );
		$data = apply_filters( 'wpcrm_system_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}