<?php
declare(strict_types=1);

namespace OCA\DomainControl\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version3885Date20251227000001 extends SimpleMigrationStep
{

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('dc_website_files')) {
            $table = $schema->createTable('dc_website_files');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('website_id', Types::BIGINT, [
                'notnull' => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('file_path', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('file_name', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('file_size', Types::BIGINT, [
                'notnull' => false,
                'default' => 0,
            ]);
            $table->addColumn('mime_type', Types::STRING, [
                'notnull' => false,
                'length' => 100,
            ]);
            $table->addColumn('category', Types::STRING, [
                'notnull' => false,
                'length' => 50,
            ]);
            $table->addColumn('description', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('updated_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['website_id'], 'dc_web_file_web_idx');
            $table->addIndex(['user_id'], 'dc_web_file_user_idx');
            $table->addIndex(['category'], 'dc_web_file_cat_idx');
        }

        return $schema;
    }
}
