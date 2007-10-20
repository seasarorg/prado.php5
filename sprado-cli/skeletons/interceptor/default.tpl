<?php
class @@CLASS_NAME@@
    extends S2Container_AbstractInterceptor {

    /**
     * @param S2Container_MethodInvocation $invocation
     *    - $invocation->getThis()      : return target object
     *    - $invocation->getMethod()    : return ReflectionMethod of target method
     *    - $invocation->getArguments() : return array of method arguments
     */
    public function invoke(S2Container_MethodInvocation $invocation) {
        return $invocation->proceed();
    }
}
