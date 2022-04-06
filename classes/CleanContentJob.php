<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\F;

final class CleanContentJob extends JanitorJob
{
    private $progress;
    private $climate;

    /**
     * based on cookbook by @texnixe
     * https://getkirby.com/docs/cookbook/extensions/content-file-cleanup
     *
     * @return array
     */
    public function job(): array
    {
        $kirby = kirby();

        // Authenticate as almighty
        $kirby->impersonate('kirby');

        // Define your collection
        // Don't use `$site->index()` for thousands of pages
        $collection = $kirby->site()->index();

        $time = microtime(true);
        $count = $collection->count();
        $updated = 0;
        $this->climate = \Bnomei\Janitor::climate();

        // set the fields to be ignored
        $ignore = option('bnomei.jabitor.cleancontentjob.ignore', ['title', 'slug', 'template', 'sort']);

        // call the script for all languages if multilang
        if ($kirby->multilang() === true) {
            $languages = $kirby->languages();
            foreach ($languages as $language) {
                $updated += $this->cleanUp($collection, $ignore, $language->code());
            }
        } else {
            $updated += $this->cleanUp($collection, $ignore);
        }

        if ($this->climate) {
            $this->climate->blue('duration: ' . round((microtime(true) - $time) * 1000) . 'ms');
            $this->climate->blue('count: ' . $count);
            $this->climate->blue('updated: ' . $updated);
        }

        return [
            'status' => $updated > 0 ? 200 : 204,
        ];
    }

    /**
     * based on cookbook by @texnixe
     * https://getkirby.com/docs/cookbook/extensions/content-file-cleanup
     */
    protected function cleanUp($collection, $ignore = null, string $lang = null): int
    {
        $updated = 0;
        foreach ($collection as $item) {
            // get all fields in the content file
            $contentFields = $item->content($lang)->fields();

            // unset all fields in the `$ignore` array
            foreach ($ignore as $field) {
                if (array_key_exists($field, $contentFields) === true) {
                    unset($contentFields[$field]);
                }
            }

            // get the keys
            $contentFields = array_keys($contentFields);

            // get all field keys from blueprint
            $blueprintFields = array_keys($item->blueprint()->fields());

            // get all field keys that are in $contentFields but not in $blueprintFields
            $fieldsToBeDeleted = array_diff($contentFields, $blueprintFields);

            // update page only if there are any fields to be deleted
            if (count($fieldsToBeDeleted) > 0) {

                // flip keys and values and set new values to null
                $data = array_map(function ($value) {
                    return null;
                }, array_flip($fieldsToBeDeleted));

                // try to update the page with the data
                try {
                    $item->update($data, $lang);
                    $updated++;
                    if ($this->climate) {
                        $this->climate->green('+++ ' . $item->id());
                    }
                } catch (\Exception $e) {
                    if ($this->climate) {
                        $this->climate->red('ERR ' . $item->id() . ': ' .$e->getMessage());
                    }
                }
            } else {
                if ($this->climate) {
                    $this->climate->white('=== ' . $item->id());
                }
            }
        }

        return $updated;
    }
}
