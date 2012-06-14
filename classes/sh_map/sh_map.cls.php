<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that builds date pickers
 */
class sh_map extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_html'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.11.03.29', '<=' ) ) {
                $this->linker->renderer->add_render_tag( 'render_map', __CLASS__, 'render_map' );
                $this->linker->renderer->add_render_tag( 'render_mapMarker', __CLASS__, 'render_mapMarker' );
                $this->linker->renderer->add_render_tag( 'render_mapImage', __CLASS__, 'render_mapImage' );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

    }

    public function render_map( $attributes = array( ) ) {
        $this->linker->html->addScript( 'http://maps.google.com/maps/api/js?sensor=false' );
        if( !isset( $attributes['id'] ) ) {
            $attributes['id'] = 'map' . substr( md5( microtime() ), 0, 12 );
        }
        $values['map']['id'] = $attributes['id'];
        $this->debug( 'Creating the map' . $values['map']['id'], 2, __LINE__ );
        $this->linker->html->addToBody( 'onload', 'initializeMap_' . $attributes['id'] . '();' );
        if( isset( $attributes['lat'] ) && !empty($attributes['lat'])) {
            $values['map']['lat'] = $attributes['lat'];
        } else {
            $values['map']['lat'] = 43.503757;
        }
        if( isset( $attributes['lng'] ) && !empty($attributes['lng'])) {
            $values['map']['lng'] = $attributes['lng'];
        } else {
            $values['map']['lng'] = 5.387467;
        }
        if( isset( $attributes['zoom'] ) ) {
            $values['map']['zoom'] = $attributes['zoom'];
        } else {
            $values['map']['zoom'] = 13;
        }
        if( isset( $attributes['width'] ) ) {
            $values['map']['width'] = $attributes['width'];
        } else {
            $values['map']['width'] = 400;
        }
        if( isset( $attributes['height'] ) ) {
            $values['map']['height'] = $attributes['height'];
        } else {
            $values['map']['height'] = 400;
        }
        $type = 'ROADMAP';
        if( isset( $attributes['type'] ) ) {
            $tempType = strtoupper( $attributes['type'] );
            if( in_array( $type, array( 'HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN' ) ) ) {
                $type = $tempType;
            }
        }
        $values['map']['type'] = 'google.maps.MapTypeId.' . $type;

        $ret = $this->render( 'render_map', $values, false, false );
        return $ret;

    }

    public function render_mapMarker( $attributes = array( ), $content = '' ) {
        if( isset( $attributes['map'] ) ) {
            $this->debug( 'Creating a marker on the map ' . $attributes['map'], 2, __LINE__ );
            $values['marker']['map'] = $attributes['map'];
        } else {
            $this->debug( 'No map attribute found for the RENDER_MAPMARKER tag', 0, __LINE__ );
            return false;
        }
        if( isset( $attributes['lat'] ) && !empty($attributes['lat'])) {
            $values['marker']['lat'] = $attributes['lat'];
        } else {
            $values['marker']['lat'] = 43.503757;
        }
        if( isset( $attributes['lng'] ) && !empty($attributes['lng'])) {
            $values['marker']['lng'] = $attributes['lng'];
        } else {
            $values['marker']['lng'] = 5.387467;
        }
        $this->debug( 'The marker should be placed at ' . $values['marker']['lat'] . ',' . $values['marker']['lng'], 3,
                      __LINE__ );
        if( isset( $attributes['image'] ) ) {
            $values['marker']['image'] = $attributes['image'];
        } else {
            $values['marker']['image'] = 'markerDefaultImage';
        }
        if( isset( $attributes['shadow'] ) ) {
            $values['marker']['shadow'] = $attributes['shadow'];
        } else {
            $values['marker']['shadow'] = 'markerDefaultShadow';
        }
        if( isset( $attributes['shape'] ) ) {
            $values['marker']['shape'] = $attributes['shape'];
        } else {
            $values['marker']['shape'] = 'markerDefaultShape';
        }
        if( isset( $attributes['title'] ) ) {
            $values['marker']['title'] = $attributes['title'];
        }
        if( isset( $attributes['draggable'] ) ) {
            $values['marker']['draggable'] = 'true';
            if( strpos( $attributes['draggable'], '|' ) > 0 ) {
                list($values['marker']['name_lat'], $values['marker']['name_lng']) = explode( '|',
                                                                                              $attributes['draggable'] );
            } else {
                $values['marker']['name'] = $attributes['draggable'];
            }
        } else {
            $values['marker']['draggable'] = 'false';
        }
        if( isset( $attributes['existing_inputs'] ) ) {
            $values['marker']['existing_inputs'] = true;
        }
        if( isset( $attributes['class'] ) ) {
            $classes = explode( ' ', $attributes['class'] );
            foreach( $classes as $class ) {
                $values['marker_classes'][]['class'] = $class;
            }
        }
        if( $attributes['open'] == 'open' ) {
            $values['marker']['open'] = true;
        }
        $values['marker']['id'] = 'marker_' . substr( md5( microtime() ), 0, 12 );
        $values['marker']['content'] = $content;


        $ret = $this->render( 'render_mapMarker', $values, false, false );
        return $ret;

    }

    public function render_mapimage( $attributes = array( ), $content = '' ) {
        if( isset( $attributes['map'] ) ) {
            $this->debug( 'Creating a mapImage on the map ' . $attributes['map'], 2, __LINE__ );
            $values['mapImage']['map'] = $attributes['map'];
        } else {
            $this->debug( 'No map attribute found for the RENDER_MAPIMAGE tag', 0, __LINE__ );
            return false;
        }
        $ok = true;
        if( isset( $attributes['north'] ) ) {
            $values['mapImage']['north'] = $attributes['north'];
        } else {
            $ok = false;
        }
        if( isset( $attributes['west'] ) ) {
            $values['mapImage']['west'] = $attributes['west'];
        } else {
            $ok = false;
        }
        if( isset( $attributes['south'] ) ) {
            $values['mapImage']['south'] = $attributes['south'];
        } else {
            $ok = false;
        }
        if( isset( $attributes['east'] ) ) {
            $values['mapImage']['east'] = $attributes['east'];
        } else {
            $ok = false;
        }
        $this->debug( 'The image should be placed between ' . $values['mapImage']['south'] . ',' . $values['mapImage']['west'] . ' to ' . $values['mapImage']['north'] . ',' . $values['mapImage']['east'],
                      3, __LINE__ );
        if( !$ok ) {
            $this->debug( 'Attributes north, west, south and east are required for the RENDER_MAPIMAGE tag', 0, __LINE__ );
            return false;
        }
        if( isset( $attributes['image'] ) ) {
            $values['mapImage']['image'] = $attributes['image'];
        } else {
            $this->debug( 'Attribute image required for the RENDER_MAPIMAGE tag', 0, __LINE__ );
            return false;
        }
        $visible = true;
        if( isset( $attributes['hidden'] ) ) {
            $visible = false;
        }
        if( $visible ) {
            $values['mapImage']['display'] = true;
        }
        if( isset( $attributes['toggler'] ) ) {
            $values['mapImage']['toggler'] = $attributes['toggler'];
        }

        $values['mapImage']['id'] = 'mapImage_' . substr( md5( microtime() ), 0, 12 );


        $ret = $this->render( 'render_mapImage', $values, false, false );
        return $ret;

    }

    public function __tostring() {
        return get_class();

    }

}

