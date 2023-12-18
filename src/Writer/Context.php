<?php

namespace Edutiek\LongEssayAssessmentService\Writer;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\Alert;
use Edutiek\LongEssayAssessmentService\Data\WritingSettings;
use Edutiek\LongEssayAssessmentService\Data\WritingStep;
use Edutiek\LongEssayAssessmentService\Data\WritingTask;
use Edutiek\LongEssayAssessmentService\Data\WrittenEssay;
use Edutiek\LongEssayAssessmentService\Data\WrittenNote;
use Edutiek\LongEssayAssessmentService\Data\WritingPreferences;

/**
 * Required interface of a context application (e.g. an LMS) calling the writer service
 * A class implementing this interface must be provided in the constructor of the writer service
 *
 * @package Edutiek\LongEssayAssessmentService\Writer
 */
interface Context extends Base\BaseContext
{
    /**
     * Get the Task that should be done in the editor
     * The instructions of this task will be shown to the student when the writer is opened
     * The writing end will limit the time for writing
     */
    public function getWritingTask(): WritingTask;

    /**
     * Get the alerts to be shown to the writer
     * @return Alert[]
     */
    public function getAlerts(): array;

    /**
     * Get the Essay that is written by the student
     * The data represents the last saving status
     * @return WrittenEssay
     */
    public function getWrittenEssay(): WrittenEssay;

    /**
     * Set the Essay that is written by the student
     * This is set when changes are sent from the editor
     */
    public function setWrittenEssay(WrittenEssay $essay): void;

    /**
     * Get the Notes written by the student
     * The data represents the last saving status
     * @return WrittenNote[]
     */
    public function getWrittenNotes(): array;

    /**
     * Set a note written by the student
     * This is set time when changes are sent from the editor
     */
    public function setWrittenNote(WrittenNote $note): void;

    /**
     * Delete the written notes
     * This is done when the written essay is authorized
     */
    public function deleteWrittenNotes() : void;

    
    /**
     * Get the preferences set by the writing user
     * @return WritingPreferences
     */
    public function getWritingPreferences(): WritingPreferences;

    /**
     * Set the preferences of the writing user
     */
    public function setWritingPreferences(WritingPreferences $preferences): void;


    /**
     * Get the writing steps that lead to the written text
     * This may return an empty array if the context does not provide a writing history
     *
     * - steps must be returned in their saving order
     * - the hash before each step must be equal to the hash after the previous step
     * - The resulting content after the last save must be equal to getWrittenText()
     * - The hash after the last step must be equal to getWrittenHash()
     *
     * @param ?int $maximum Maximum number of provided steps (from the end). Get all steps, if not set
     * @return WritingStep[]
     */
    public function getWritingSteps(?int $maximum): array;

    /**
     * Add writing steps to the history
     * This may be ignored if the context does not provide a writing history
     * @param WritingStep[] $steps
     */
    public function addWritingSteps(array $steps);

    /**
     * Check if a writing step with a hash after application already exists
     * This is used to ensure a correct sequence of writing steps
     * Note: the hash is a combination of the resulting text and the timestamp and therefore unique
     */
    public function hasWritingStepByHashAfter(string $hash_after): bool;
}