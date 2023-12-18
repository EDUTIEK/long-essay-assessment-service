<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class WrittenEssay
{

    protected ?string $written_text;
    protected ?string $written_hash;
    protected int $service_version;
    protected ?int $edit_started;
    protected ?int $edit_ended;
    protected bool $is_authorized;

    private ?int $writing_authorized;
    private ?string $writing_authorized_by;
    private ?int $correction_finalized;
    private ?string $correction_finalized_by;
    private ?float $final_points;
    private ?string $final_grade;
    private ?string $stitch_comment;


    /**
     * Constructor (see getters)
     */
    public function __construct(
        ?string $written_text,
        ?string $written_hash,
        int $service_version,
        ?int $edit_started,
        ?int $edit_ended,
        bool $is_authorized,

        // for documentation
        ?int $writing_authorized = null,
        ?string $writing_authorized_by = null,
        ?int $correction_finalized = null,
        ?string $correction_finalized_by = null,
        ?float $final_points = null,
        ?string $final_grade = null,
        ?string $stitch_comment = null
    ) {
        $this->written_text = $written_text;
        $this->written_hash = $written_hash;
        $this->service_version = $service_version;
        $this->edit_started = $edit_started;
        $this->edit_ended = $edit_ended;
        $this->is_authorized = $is_authorized;

        $this->writing_authorized = $writing_authorized;
        $this->writing_authorized_by = $writing_authorized_by;
        $this->correction_finalized = $correction_finalized;
        $this->correction_finalized_by = $correction_finalized_by;
        $this->final_points = $final_points;
        $this->final_grade = $final_grade;
        $this->stitch_comment = $stitch_comment;
    }


    /**
     * Get the raw text (html) written by the user
     */
    public function getWrittenText(): ?string
    {
        return $this->written_text;
    }

    /**
     * Apply the raw text (html) written by the user
     */
    public function withWrittenText(?string $written_text): self
    {
        $this->written_text = $written_text;
        return $this;
    }

    /**
     * Get the hash value of the raw text written by the user
     */
    public function getWrittenHash(): ?string
    {
        return $this->written_hash;
    }

    /**
     * Apply the hash value of the raw text written by the user
     */
    public function withWrittenHash(?string $written_hash): self
    {
        $this->written_hash = $written_hash;
        return $this;
    }


    /**
     * Get the services version of the last text saving
     */
    public function getServiceVersion(): int
    {
        return $this->service_version;
    }

    /**
     * Apply the service version of the last text saving
     * This must be applied each time an essay is saved with a new text
     */
    public function withServiceVersion(int $service_version): WrittenEssay
    {
        $this->service_version = $service_version;
        return $this;
    }

    /**
     * Get the unix timestamp of the time when the user started writing
     */
    public function getEditStarted(): ?int
    {
        return $this->edit_started;
    }

    /**
     * Apply the unix timestamp of the time when the user started writing
     */
    public function withEditStarted(?int $edit_started): self
    {
        $this->edit_started = $edit_started;
        return $this;
    }

    /**
     * Get the unix timestamp of the time when the user ended writing
     */
    public function getEditEnded(): ?int
    {
        return $this->edit_ended;
    }

    /**
     * Apply the unix timestamp of the time when the user ended writing
     */
    public function withEditEnded(?int $edit_ended): self
    {
        $this->edit_ended = $edit_ended;
        return $this;
    }


    /**
     * Get the authorization status by the writing user
     */
    public function isAuthorized()
    {
        return $this->is_authorized;
    }

    /**
     * Apply the authorization status by the writing user
     */
    public function withIsAuthorized(bool $is_authorized) : self
    {
        $this->is_authorized = $is_authorized;
        return $this;
    }


    /**
     * Get the unix timestamp when the writing is authorized
     */
    public function getWritingAuthorized(): ?int
    {
        return $this->writing_authorized;
    }


    /**
     * Apply the unix timestamp when the writing is authorized
     */
    public function setWritingAuthorized(?int $writing_authorized): WrittenEssay
    {
        $this->writing_authorized = $writing_authorized;
        return $this;
    }



    /**
     * Get the name of the person that authorized the writing
     */
    public function getWritingAuthorizedBy(): ?string
    {
        return $this->writing_authorized_by;
    }

    /**
     * Apply the name of the person that authorized the writing
     */
    public function setWritingAuthorizedBy(?string $writing_authorized_by): WrittenEssay
    {
        $this->writing_authorized_by = $writing_authorized_by;
        return $this;
    }


    /**
     * Get the unix timestamp when the correction is finalized
     */
    public function getCorrectionFinalized(): ?int
    {
        return $this->correction_finalized;
    }

    /**
     * Apply the unix timestamp when the correction is finalized
     */
    public function withCorrectionFinalized(?int $correction_finalized): WrittenEssay
    {
        $this->correction_finalized = $correction_finalized;
        return $this;
    }

    /**
     * Get the name of the person that finalized the correction
     */
    public function getCorrectionFinalizedBy(): ?string
    {
        return $this->correction_finalized_by;
    }

    /**
     * Apply the name of the person that finalized the correction
     */
    public function withCorrectionFinalizedBy(?string $correction_finalized_by): WrittenEssay
    {
        $this->correction_finalized_by = $correction_finalized_by;
        return $this;
    }

    /**
     * Get the final given points
     */
    public function getFinalPoints(): ?float
    {
        return $this->final_points;
    }

    /**
     * Apply the final given points
     */
    public function withFinalPoints(?float $final_points): WrittenEssay
    {
        $this->final_points = $final_points;
        return $this;
    }


    /**
     * Get the final grade level title
     */
    public function getFinalGrade(): ?string
    {
        return $this->final_grade;
    }

    /**
     * Apply the final grade level title
     */
    public function withFinalGrade(?string $final_grade): WrittenEssay
    {
        $this->final_grade = $final_grade;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStitchComment(): ?string
    {
        return $this->stitch_comment;
    }

    /**
     * @param string|null $stitch_comment
     * @return WrittenEssay
     */
    public function withStitchComment(?string $stitch_comment): WrittenEssay
    {
        $this->stitch_comment = $stitch_comment;
        return $this;
    }
}