<?php

namespace App\Http\Controllers\Traits;

use Barryvdh\TranslationManager\Manager;

class MyImportManager extends Manager
{
    public function getDirectories(){
        return $this->files;
    }
    public function importTranslationsFile( $replace = false, $base = null )
    {
        $counter = 0;
        //allows for vendor lang files to be properly recorded through recursion.
        $vendor = true;
        if ( $base == null ) {
            $base   = $this->app[ 'path.lang' ];
            $vendor = false;
        }

        foreach ( $this->getDirectories()->directories( $base ) as $langPath ) {
            $locale = basename( $langPath );

            //import langfiles for each vendor

            $vendorName = $this->files->name( $this->files->dirname( $langPath ) );
            foreach ( $this->files->allfiles( $langPath ) as $file ) {
                $info  = pathinfo( $file );
                $group = $info[ 'filename' ];
                if($group == 'custom-menu'){
                    if ( in_array( $group, $this->config[ 'exclude_groups' ] ) ) {
                        continue;
                    }
                    $subLangPath = str_replace( $langPath . DIRECTORY_SEPARATOR, '', $info[ 'dirname' ] );
                    $subLangPath = str_replace( DIRECTORY_SEPARATOR, '/', $subLangPath );
                    $langPath    = str_replace( DIRECTORY_SEPARATOR, '/', $langPath );

                    if ( $subLangPath != $langPath ) {
                        $group = $subLangPath . '/' . $group;
                    }

                    if ( !$vendor ) {
                        $translations = \Lang::getLoader()->load( $locale, $group );
                    } else {
                        $translations = include( $file );
                        $group        = "vendor/" . $vendorName;
                    }

                    if ( $translations && is_array( $translations ) ) {
                        foreach ( \Illuminate\Support\Arr::dot( $translations ) as $key => $value ) {
                            $importedTranslation = $this->importTranslation( $key, $value, $locale, $group, $replace );
                            $counter             += $importedTranslation ? 1 : 0;
                        }
                    }
                }
            }
        }

        foreach ( $this->files->files( $this->app[ 'path.lang' ] ) as $jsonTranslationFile ) {
            if ( strpos( $jsonTranslationFile, '.json' ) === false ) {
                continue;
            }
            $locale       = basename( $jsonTranslationFile, '.json' );
            $group        = self::JSON_GROUP;
            $translations =
                \Lang::getLoader()->load( $locale, '*', '*' ); // Retrieves JSON entries of the given locale only
            if ( $translations && is_array( $translations ) ) {
                foreach ( $translations as $key => $value ) {
                    $importedTranslation = $this->importTranslation( $key, $value, $locale, $group, $replace );
                    $counter             += $importedTranslation ? 1 : 0;
                }
            }
        }
        return $counter;
    }

}