<?php

namespace ESD\Yii\Gii\Console;

use ESD\Yii\Console\Controller;
use ESD\Yii\Helpers\Console;
use ESD\Yii\Yii;

class GenerateController extends Controller
{
    /**
     * @var boolean whether to overwrite all existing code files when in non-interactive mode.
     * Defaults to false, meaning none of the existing code files will be overwritten.
     * This option is used only when `--interactive=0`.
     */
    public $overwrite = false;

    /**
     * @var \ESD\Yii\Gii\Generator | \ESD\Yii\Gii\Generators\Model\Generator
     */
    public $generator;

    /**
     * @param string $id
     * @param array|null $params
     * @return int|null
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function runAction(string $id, ?array $params = []): ?int
    {
        $generator = $this->getGenerator($id);
        /** @var \ESD\Yii\Gii\Generator | \ESD\Yii\Gii\Generators\Model\Generator $generatorObject */
        $this->generator = Yii::createObject(array_merge($generator, $params));

        if ($this->generator->validate()) {
            $this->generateCode();
        } else {
            $this->displayValidationErrors();
        }

        return 0;
    }

    /**
     * @return void
     */
    protected function displayValidationErrors()
    {
        $this->stdout("Code not generated. Please fix the following errors:\n\n", Console::FG_RED);
        foreach ($this->generator->errors as $attribute => $errors) {
            echo ' - ' . $this->ansiFormat($attribute, Console::FG_CYAN) . ': ' . implode('; ', $errors) . "\n";
        }
        echo "\n";
    }

    /**
     * @return void
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function generateCode()
    {
        $files = $this->generator->generate();
        $n = count($files);
        if ($n === 0) {
            echo "No code to be generated.\n";
            return;
        }
        echo "The following files will be generated:\n";
        $skipAll = $this->interactive ? null : !$this->overwrite;
        $answers = [];
        foreach ($files as $file) {
            $path = $file->getRelativePath();
            if (is_file($file->path)) {
                if (file_get_contents($file->path) === $file->content) {
                    echo '  ' . $this->ansiFormat('[unchanged]', Console::FG_GREY);
                    echo $this->ansiFormat(" $path\n", Console::FG_CYAN);
                    $answers[$file->id] = false;
                } else {
                    echo '    ' . $this->ansiFormat('[changed]', Console::FG_RED);
                    echo $this->ansiFormat(" $path\n", Console::FG_CYAN);
                    if ($skipAll !== null) {
                        $answers[$file->id] = !$skipAll;
                    } else {
                        $answer = $this->select("Do you want to overwrite this file?", [
                            'y' => 'Overwrite this file.',
                            'n' => 'Skip this file.',
                            'ya' => 'Overwrite this and the rest of the changed files.',
                            'na' => 'Skip this and the rest of the changed files.',
                        ]);
                        $answers[$file->id] = $answer === 'y' || $answer === 'ya';
                        if ($answer === 'ya') {
                            $skipAll = false;
                        } elseif ($answer === 'na') {
                            $skipAll = true;
                        }
                    }
                }
            } else {
                echo '        ' . $this->ansiFormat('[new]', Console::FG_GREEN);
                echo $this->ansiFormat(" $path\n", Console::FG_CYAN);
                $answers[$file->id] = true;
            }
        }

        if (!array_sum($answers)) {
            $this->stdout("\nNo files were chosen to be generated.\n", Console::FG_CYAN);
            return;
        }

        if (!$this->confirm("\nReady to generate the selected files?", true)) {
            $this->stdout("\nNo file was generated.\n", Console::FG_CYAN);
            return;
        }

        if ($this->generator->save($files, (array)$answers, $results)) {
            $this->stdout("\nFiles were generated successfully!\n", Console::FG_GREEN);
        } else {
            $this->stdout("\nSome errors occurred while generating the files.", Console::FG_RED);
        }
        echo preg_replace('%<span class="error">(.*?)</span>%', '\1', $results) . "\n";
    }

    /**
     * Return the special code generator
     *
     * @param string $type
     * @return array|null
     */
    public function getGenerator(string $type): ?array
    {
        $coreGenerators = $this->coreGenerators();
        if (!empty($coreGenerators[$type])) {
            return $coreGenerators[$type];
        }
        return null;
    }

    /**
     * Returns the list of the core code generator configurations.
     * @return array the list of the core code generator configurations.
     */
    protected function coreGenerators(): array
    {
        return [
            'model' => ['class' => 'ESD\Yii\Gii\Generators\Model\Generator'],
        ];
    }
}
