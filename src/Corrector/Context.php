<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\CorrectionItem;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSettings;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSummary;
use Edutiek\LongEssayAssessmentService\Data\CorrectionTask;
use Edutiek\LongEssayAssessmentService\Data\Corrector;
use Edutiek\LongEssayAssessmentService\Data\CorrectionGradeLevel;
use Edutiek\LongEssayAssessmentService\Data\WrittenEssay;
use Edutiek\LongEssayAssessmentService\Exceptions\ContextException;
use Edutiek\LongEssayAssessmentService\Data\CorrectionRatingCriterion;
use Edutiek\LongEssayAssessmentService\Data\CorrectionComment;
use Edutiek\LongEssayAssessmentService\Data\CorrectionPoints;

/**
 * Required interface of a context application (e.g. an LMS) calling the corrector service
 * A class implementing this interface must be provided in the constructor of the corrector service
 *
 * @package Edutiek\LongEssayAssessmentService\Corrector
 */
interface Context extends Base\BaseContext
{
    /**
     * Get the correction that should be done in the app
     * The title and instructions are shown in the app
     * The correction end will limit the time for correction
     */
    public function getCorrectionTask(): CorrectionTask;

    /**
     * Get the correction settings for the app
     */
    public function getCorrectionSettings() : CorrectionSettings;

    /**
     * Get the grade levels defined in the environment
     * @return CorrectionGradeLevel[]
     */
    public function getGradeLevels(): array;

    /**
     * Get the rating criteria defined in the environment
     * @return CorrectionRatingCriterion[]
     */
    public function getRatingCriteria(): array;


    /**
     * Get the items that are assigned to the current user for correction
     * These items can be stepped through in the corrector app
     * @param bool $use_filter    apply a filter set by the user
     * @return CorrectionItem[]
     */
    public function getCorrectionItems(bool $use_filter = false): array;


    /**
     * Get the current corrector
     * This corrector represents the current user
     * If the current user is no corrector (e.g. for review decision or stitch decision), return null
     */
    public function getCurrentCorrector(): ?Corrector;


    /**
     * Get the current correction item
     * This item should be initially loaded in the corrector app
     * It must be an item in the list provided by getCorrectionItems()
     */
    public function getCurrentItem(): ?CorrectionItem;


    /**
     * Get the written essay by the key of a correction item
     */
    public function getEssayOfItem(string $item_key): ?WrittenEssay;


    /**
     * Get the correctors assigned to a correction item
     * @return Corrector[]
     */
    public function getCorrectorsOfItem(string $item_key): array;


    /**
     * Get the correction summary given by a corrector for a correction item
     */
    public function getCorrectionSummary(string $item_key, string $corrector_key): ?CorrectionSummary;


    /**
     * Get the correction comments given by a corrector for a correction item
     * @return CorrectionComment[]
     */
    public function getCorrectionComments(string $item_key, string $corrector_key): array;

    /**
     * Get the correction points given by a corrector for a correction item
     * @return CorrectionPoints[]
     */
    public function getCorrectionPoints(string $item_key, string $corrector_key): array;

    /**
     * Set the text of the written essay that is processed for display in the corrector (e.g. line numbers added)
     */
    public function setProcessedText(string $item_key, ?string $text) : void;


    /**
     * Set the correction summary given by a corrector for a correction item
     */
    public function setCorrectionSummary(string $item_key, string $corrector_key, CorrectionSummary $summary) : void;

    /**
     * Save a correction comment if it belongs to a corrector
     * Returns the id of the saved or deleted comment 
     * Returns null if the comment can't be saved
     */
    public function saveCorrectionComment(CorrectionComment $comment, string $corrector_key): ?int;


    /**
     * Delete a correction comment if it belongs to a corrector
     * 
     * Returns true if the comment is deleted afterwards
     * Returns false if the comment can't be deleted
     */
    public function deleteCorrectionComment(string $comment_key, string $corrector_key): bool;


    /**
     * Save a correction points object if it belongs to a corrector
     * Returns the id of the saved or deleted points
     * Returns null if the points can't be saved
     */
    public function saveCorrectionPoints(CorrectionPoints $points, string $corrector_key): ?int;


    /**
     * Delete a correction points object if it belongs to a corrector
     *
     * Returns true if the object is deleted afterwards
     * Returns false if the object can't be deleted
     */
    public function deleteCorrectionPoints(string $points_key, string $corrector_key): bool;


    /**
     * Save a stitch decision
     */
    public function saveStitchDecision(string $item_key, int $timestamp, ?float $points, ?string $grade_key, ?string $stitch_comment) : bool;


    /**
     * Set the review mode for the corrector
     * This must be called after init()
     * @throws ContextException if user is not allowed to review corrections
     */
    public function setReview(bool $is_review);


    /**
     * Get if the corrector should be opened for review of all correctors
     */
    public function isReview() : bool;


    /**
     * Set the stitch decision mode for the corrector
     * @throws ContextException if user is not allowed to draw stitch decisions
     */
    public function setStitchDecision(bool $is_stitch_decision);


    /**
     * Get if the corrector should be opened for a stitch decision
     */
    public function isStitchDecision() : bool;
}
