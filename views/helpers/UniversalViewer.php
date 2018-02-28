<?php
/**
 * Helper to display a Universal Viewer
 */
class UniversalViewer_View_Helper_UniversalViewer extends Zend_View_Helper_Abstract
{
    /**
     * Get the specified UniversalViewer.
     *
     * @param Record $record
     * @param array $options Associative array of optional values:
     *   - (string) class
     *   - (string) locale
     *   - (string) style
     *   - (string) config
     * @return string. The html string corresponding to the UniversalViewer.
     */
    public function universalViewer($record, $options = array())
    {
        if (empty($record)) {
            return '';
        }

        // Prepare the url of the manifest for a dynamic collection.
        if (is_array($record)) {
            $identifier = $this->_buildIdentifierForList($record);
            $route = 'universalviewer_presentation_collection_list';
            $urlManifest = absolute_url(array(
                 'id' => $identifier,
            ), $route);
            $urlManifest = $this->view->uvForceBaseUrlIfRequired($urlManifest);
            return $this->_display($urlManifest, $options);
        }

        // Prepare the url for the manifest of a record after additional checks.
        $recordClass = get_class($record);
        if (!in_array($recordClass, array('Item', 'Collection'))) {
            return '';
        }

        // Determine if we should get the manifest from a field in the metadata.
        $urlManifest = '';
        $manifestElement = get_option('universalviewer_alternative_manifest_element');
        if ($manifestElement) {
            $urlManifest = metadata($record, json_decode($manifestElement, true));
            if ($urlManifest) {
                return $this->_display($urlManifest, $options);
            }
            // If manifest not provided in metadata, point to manifest created
            // from Omeka files.
        }

        // Some specific checks.
        switch ($recordClass) {
            case 'Item':
                // Currently, an item without files is unprocessable.
                if ($record->fileCount() == 0) {
                    // return __('This item has no files and is not displayable.');
                    return '';
                }
                $route = 'universalviewer_presentation_item';
                break;
            case 'Collection':
                if ($record->totalItems() == 0) {
                    // return __('This collection has no item and is not displayable.');
                    return '';
                }
                $route = 'universalviewer_presentation_collection';
                break;
        }

        $urlManifest = absolute_url(array(
            'id' => $record->id,
        ), $route);
        $urlManifest = $this->view->uvForceBaseUrlIfRequired($urlManifest);

        return $this->_display($urlManifest, $options);
    }

    /**
     * Helper to create an identifier from a list of records.
     *
     * The dynamic identifier is a flat list of ids: "c-5i-1,i-2,c-3".
     * If there is only one id, a comma is added to avoid to have the same route
     * than the collection itself.
     * If there are only items, the most common case, the dynamic identifier is
     * simplified: "1,2,6". In all cases the order of records is kept.
     *
     * @todo Merge with UniversalViewer_View_Helper_IiifCollectionList::_buildIdentifierForList()
     *
     * @param array $records
     * @return string
     */
    protected function _buildIdentifierForList($records)
    {
        $map = array(
            'Item' => 'i-',
            'Collection' => 'c-',
            'File' => 'm-',
        );

        $identifiers = array();
        foreach ($records as $record) {
            $identifiers[] = $map[get_class($record)] . $record->id;
        }

        $identifier = implode(',', $identifiers);

        if (count($identifiers) == 1) {
            $identifier .= ',';
        }

        // Simplify the identifier: remove the "i-" if there are only items.
        if (strpos($identifier, 'c') === false && strpos($identifier, 'm') === false) {
            $identifier = str_replace('i-', '', $identifier);
        }

        return $identifier;
    }

    /**
     * Render a universal viewer for a url, according to options.
     *
     * @param string $urlManifest
     * @param array $options
     * @return string
     */
    protected function _display($urlManifest, $options = array())
    {
        $urlJs = src('UniversalViewer', 'javascripts', 'js');
        $uvJs = src('uv', 'javascripts/uv', 'js');

        $html = '';
        $html .= sprintf('<div id="uv" class="uv" data-manifest="%s"></div>', $urlManifest);
        $html .= sprintf('<script type="text/javascript" src="%s"></script>', $urlJs);
        $html .= sprintf('<script type="text/javascript" src="%s"></script>', $uvJs);

        return $html;
    }
}
