<?php
declare(strict_types=1);


namespace Ecotone\Messaging\Config\Annotation\ModuleConfiguration;

use Ecotone\AnnotationFinder\AnnotationFinder;
use Ecotone\Messaging\Annotation\ModuleAnnotation;
use Ecotone\Messaging\Config\Annotation\AnnotationModule;
use Ecotone\Messaging\Config\Annotation\AnnotationRegistrationService;
use Ecotone\Messaging\Config\Configuration;
use Ecotone\Messaging\Config\ModuleReferenceSearchService;
use Ecotone\Messaging\Handler\Gateway\ErrorChannelInterceptor;
use Ecotone\Messaging\Handler\InterfaceToCall;
use Ecotone\Messaging\Handler\Logger\Annotation\LogAfter;
use Ecotone\Messaging\Handler\Logger\Annotation\LogBefore;
use Ecotone\Messaging\Handler\Logger\Annotation\LogError;
use Ecotone\Messaging\Handler\Logger\ExceptionLoggingInterceptorBuilder;
use Ecotone\Messaging\Handler\Logger\LoggingHandlerBuilder;
use Ecotone\Messaging\Handler\Logger\LoggingInterceptor;
use Ecotone\Messaging\Handler\Processor\MethodInvoker\AroundInterceptorReference;
use Ecotone\Messaging\Handler\Processor\MethodInvoker\MethodInterceptor;
use Ecotone\Messaging\Precedence;

#[ModuleAnnotation]
class LoggingModule extends NoExternalConfigurationModule implements AnnotationModule
{
    /**
     * @inheritDoc
     */
    public static function create(AnnotationFinder $annotationRegistrationService): static
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return "loggingModule";
    }

    /**
     * @inheritDoc
     */
    public function prepare(Configuration $configuration, array $extensionObjects, ModuleReferenceSearchService $moduleReferenceSearchService): void
    {
        $configuration->registerBeforeMethodInterceptor(
            MethodInterceptor::create(
                "beforeLog",
                InterfaceToCall::create(LoggingInterceptor::class, "logBefore"),
                LoggingHandlerBuilder::createForBefore(),
                Precedence::ERROR_CHANNEL_PRECEDENCE - 1,
                LogBefore::class
            )
        );
        $configuration->registerAfterMethodInterceptor(
            MethodInterceptor::create(
                "afterLog",
                InterfaceToCall::create(LoggingInterceptor::class, "logAfter"),
                LoggingHandlerBuilder::createForAfter(),
                Precedence::ERROR_CHANNEL_PRECEDENCE - 1,
                LogAfter::class
            )
        );
        $configuration->registerAroundMethodInterceptor(
            AroundInterceptorReference::createWithObjectBuilder(
                "errorLog",
                new ExceptionLoggingInterceptorBuilder(),
                "logException",
                Precedence::ERROR_CHANNEL_PRECEDENCE - 1,
                LogError::class
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function canHandle($extensionObject): bool
    {
        return false;
    }
}