<?php

namespace Edutiek\LongEssayAssessmentService\Corrector;
use Edutiek\LongEssayAssessmentService\Base;
use Edutiek\LongEssayAssessmentService\Data\CorrectionItem;
use Edutiek\LongEssayAssessmentService\Data\PageData;
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
use Edutiek\LongEssayAssessmentService\Data\CorrectionPreferences;
use Edutiek\LongEssayAssessmentService\Data\WritingAnnotation;
use Edutiek\LongEssayAssessmentService\Data\CorrectionSnippet;

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
     * Get the fixed correction settings for the app
     */
    public function getCorrectionSettings() : CorrectionSettings;

    /**
     * Get the correction preferences which can be set in the app
     * Corrector key may be null in review mode
     */
    public function getCorrectionPreferences(?string $corrector_key) : CorrectionPreferences;


    /**
     * Get the grade levels defined in the environment
     * @return CorrectionGradeLevel[]
     */
    public function getGradeLevels(): array;

    /**
     * Get the rating criteria defined in the environment
     * these can be either fixed or individual criteria of the correctors
     *
     * @see CorrectionRatingCriterion::getCorrectorKey()
     * @return CorrectionRatingCriterion[]
     */
    public function getRatingCriteria(?string $corrector_key = null): array;


    /**
     * Get the items that are assigned to the current user for correction
     * These items can be stepped through in the corrector app
     * @param bool $use_filter    apply a filter set by the user
     * @return CorrectionItem[]
     */
    public function getCorrectionItems(bool $use_filter = false): array;


    /**
     * Get the key of the current corrector
     * This corrector represents the current user
     * If the current user is no corrector (e.g. for review decision or stitch decision), return null
     */
    public function getCurrentCorrectorKey(): ?string;
    

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
     * Get the list of pdf pages from an uploaded essay of a correction item
     * @return PageData[]
     */
    public function getPagesOfItem(string $item_key): array;

    /**
     * Get the correctors assigned to a correction item
     * @return Corrector[]
     */
    public function getCorrectorsOfItem(string $item_key): array;

    /**
     * Get if a corrector is assigned to an item
     */
    public function isCorrectorOfItem(string $item_key, string $corrector_key) : bool;

    /**
     * Get the correction snippets
     * @return CorrectionSnippet[]
     */
    public function getCorrectionSnippets(string $corrector_key): array;
    
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
     * Get the correction points given by a corrector for a correction item and his rating criteria
     * Optionally restrict these to the points related to a correction comment
     * @return CorrectionPoints[]
     */
    public function getCorrectionPoints(string $item_key, string $corrector_key, ?string $comment_key = null): array;


    /**
     * Save the correction preferences given by a corrector
     * @return bool preferences are saved
     */
    public function saveCorrectionPreferences(CorrectionPreferences $preferences) : bool;

    /**
     * Save a correction snippet
     */
    public function saveCorrectionSnippet(string $corrector_key, CorrectionSnippet $snippet): void;

    /**
     * Delete a correction snippet if it belongs to a corrector
     *
     * @param string $corrector_key key of the corrector for which the snippet should be deleted
     * @param string $key key of the snippet object to delete
     */
    public function deleteCorrectionSnippet(string $corrector_key, string $key): void;

    /**
     * Save the correction summary given by a corrector for a correction item
     * @return bool summary is saved
     */
    public function saveCorrectionSummary(CorrectionSummary $summary) : bool;

    /**
     * Save a correction comment if it is valid
     * Create a new comment if a comment with the given key (e.g. temporary key) is not found
     * 
     * @param CorrectionComment $comment the comment object to be saved
     * @return string key of the saved comment or null if the comment can't be saved
     */
    public function saveCorrectionComment(CorrectionComment $comment): ?string;

    /**
     * Delete a correction comment if it belongs to a corrector
     *
     * @param string $comment_key key of the comment object to delete
     * @param string $corrector_key key of the corrector for which the comment should be deleted
     * @return bool true if the object is deleted afterward, false if the object can't be deleted
     */
    public function deleteCorrectionComment(string $comment_key, string $corrector_key): bool;


    /**
     * Save a correction points object
     * Create a new points object if points with the given key (e.g. temporary key) are not found
     *
     * @param CorrectionPoints $points the points object to be saved
     * @return string key of the saved points or null if the points can't be saved
     */
    public function saveCorrectionPoints(CorrectionPoints $points): ?string;


    /**
     * Delete a correction points object if it belongs to a corrector
     *
     * @param string $points_key key of the points object to delete
     * @param string $corrector_key key of the corrector for which the points should be deleted 
     * @return bool true if the object is deleted afterwards, false if the object can't be deleted
     */
    public function deleteCorrectionPoints(string $points_key, string $corrector_key): bool;


    /**
     * Save a stitch decision
     */
    public function saveStitchDecision(string $item_key, int $timestamp, ?float $points, ?string $grade_key, ?string $stitch_comment) : bool;


    /**
     * Set the review mode for the corrector frontend
     * This must be called from the system after init() when the frontend is opened for review
     * It is called by the services after init() when REST calls are processed
     * @throws ContextException if user is not allowed to review corrections
     */
    public function setReview(bool $is_review);


    /**
     * Get if the corrector app is opened for review of all correctors
     */
    public function isReview() : bool;


    /**
     * Set the stitch decision mode for the corrector
     * This must be called from the system after init() when the frontend is opened for review
     * It is called by the services after init() when REST calls are processed
     * @throws ContextException if user is not allowed to draw stitch decisions
     */
    public function setStitchDecision(bool $is_stitch_decision);


    /**
     * Get if the corrector should be opened for a stitch decision
     */
    public function isStitchDecision() : bool;
}
