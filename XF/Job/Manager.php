<?php

namespace GoDeferred\XF\Job;

use GoDeferred\Util\Queue;

class Manager extends XFCP_Manager
{
    public function scheduleRunTimeUpdate()
    {
        if (Queue::canEnqueueViaHeader()) {
            $this->updateNextRunTime();
            return;
        }

        parent::scheduleRunTimeUpdate();
    }

    public function setNextAutoRunTime($time)
    {
        if (Queue::enqueue($time) === true) {
            return;
        }

        parent::setNextAutoRunTime($time);
    }

    public function updateNextRunTime()
    {
        // TODO: find a better way to override this
        $runTime = $this->getFirstAutomaticTime();
        $this->setNextAutoRunTime($runTime);

        return $runTime;
    }
}
