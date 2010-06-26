<?php
class CucumberScenario {

    // provide a place we can store data
    public $aGlobals = array();

    private $aWorld;

    function __construct($_aWorld = array()) {
        $this->aWorld = $_aWorld;
    }

    function invokeBeforeHooks($aTags) {
        foreach ($this->aWorld['before'] as $aBeforeHook) {
            if (count(array_intersect($aTags, $aBeforeHook['tags'])) > 0) {
                $oStep = new $aBeforeHook['class']($this->aGlobals);
                $oResult = $oStep->$aBeforeHook['method']();
                if ($oResult === false) {
                    return array('failure');
                }
            }
        }
        return array('success');
    }

    function invokeAfterHooks($aTags) {
        foreach ($this->aWorld['after'] as $aAfterHook) {
            if (count(array_intersect($aTags, $aAfterHook['tags'])) > 0) {
                $oStep = new $aAfterHook['class']($this->aGlobals);
                $oResult = $oStep->$aAfterHook['method']();
                if ($oResult === false) {
                    return array('failure');
                }
            }
        }
        return array('success');
    }

    /**
     * @param  $iStepId
     * @param  $aArgs
     * @return mixed
     *
     * Invokes a step.  Steps can use PHPUnit assertions and will
     * mark themselves as pending if the self::markTestIncomplete() or self:markTestSkipped()
     * functions are called.  Failed expectations are returned as messages while all other
     * Exceptions are reported back as exceptions.
     */
    function invoke($iStepId, $aArgs) {
        $aStep = $this->aWorld['steps'][$iStepId];
        $oStep = new $aStep['class']($this->aGlobals);
        try {
            call_user_func_array(array($oStep, $aStep['method']),$aArgs);
        } catch (PHPUnit_Framework_IncompleteTestError $e) {
            return array('pending',$e->getMessage());
        } catch (PHPUnit_Framework_SkippedTestError $e) {
            return array('pending',$e->getMessage());
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            return array('fail', array('message' => $e->getMessage()));
        } catch (Exception $e) {
            return array('fail', array('exception' => $e->__toString()));            
        }
        return array('success');
    }

    

}
?>