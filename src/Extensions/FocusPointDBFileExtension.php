<?php

namespace JonoM\FocusPoint\Extensions;

use JonoM\FocusPoint\FieldType\DBFocusPoint;

class FocusPointDBFileExtension extends FocusPointExtension
{
    /**
     * Get focus point for this image; Prevent failover to backend Image
     *
     * @return DBFocusPoint
     */
    public function getFocusPoint(): ?DBFocusPoint
    {
        if (property_exists($this->owner, 'focuspoint_object')) {
            return $this->owner->focuspoint_object;
        }

        return null;
    }

    /**
     * Set a new focus point
     *
     * @param DBFocusPoint|null $point
     * @return $this
     */
    public function setFocusPoint(?DBFocusPoint $point): self
    {
        $this->owner->focuspoint_object = $point;
        return $this;
    }
}
