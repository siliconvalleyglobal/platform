<?php declare(strict_types=1);

namespace Shopware\Core\Content\Test\Media\Metadata;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\Metadata\Metadata;
use Shopware\Core\Content\Media\Metadata\MetadataLoader;
use Shopware\Core\Content\Media\Metadata\Type\DocumentMetadata;
use Shopware\Core\Content\Media\Metadata\Type\ImageMetadata;
use Shopware\Core\Content\Media\Metadata\Type\NoMetadata;
use Shopware\Core\Content\Media\Metadata\Type\VideoMetadata;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class MetadataLoaderTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testJpg()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/shopware.jpg'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());
        $this->assertImageMetadata($result, 1021, 1530);
    }

    public function testGif()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/logo.gif'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());
        $this->assertImageMetadata($result, 37, 142);
    }

    public function testPng()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/shopware-logo.png'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());
        $this->assertImageMetadata($result, 266, 499);
    }

    public function testSvg()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/logo-version-professionalplus.svg'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());
        $this->assertNoMetadata($result);
    }

    public function testPdf()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/Shopware_5_3_Broschuere.pdf'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata(), print_r($result, true));
        $this->assertDocumentMetadata($result, 19, 'Adobe InDesign CC 13.0 (Macintosh)', null);
    }

    public function testMp4()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/small.mp4'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());

        $this->assertImageMetadata($result, 320, 560);
        $this->assertVideoMetadata($result, 30.0);
    }

    public function testWebm()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/small.webm'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());

        $this->assertImageMetadata($result, 320, 560);
        $this->assertVideoMetadata($result, 30.0);
    }

    public function testAvi()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/small.avi'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        self::assertArrayNotHasKey('error', $result->getRawMetadata());

        $this->assertImageMetadata($result, 320, 560);
        $this->assertVideoMetadata($result, 30.0);
    }

    public function testDoc()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/reader.doc'));

        self::assertCount(1, $result->getRawMetadata(), print_r($result, true));
        $this->assertDocumentMetadata($result, null, '', '');
    }

    public function testDocx()
    {
        $result = $this
            ->getMetadataLoader()
            ->loadFromFile($this->createMediaFile(__DIR__ . '/../fixtures/reader.docx'));

        self::assertCount(2, $result->getRawMetadata(), print_r($result, true));
        $this->assertDocumentMetadata($result, null, 'PHPWord', 'A Word Document');
    }

    private function getMetadataLoader(): MetadataLoader
    {
        return $this->getContainer()
            ->get(MetadataLoader::class);
    }

    private function createMediaFile(string $filePath): MediaFile
    {
        return new MediaFile(
            $filePath,
            mime_content_type($filePath),
            pathinfo($filePath, PATHINFO_EXTENSION),
            filesize($filePath)
        );
    }

    private function assertImageMetadata(Metadata $result, int $width = null, int $height = null): void
    {
        $type = $result->getType();
        self::assertInstanceOf(ImageMetadata::class, $type);

        $this->getMetadataLoader()->updateMetadata($result);

        self::assertSame($width, $type->getWidth());
        self::assertSame($height, $type->getHeight());
    }

    private function assertVideoMetadata(Metadata $result, float $frameRate): void
    {
        $type = $result->getType();
        self::assertInstanceOf(VideoMetadata::class, $type);

        $this->getMetadataLoader()->updateMetadata($result);

        self::assertSame($frameRate, $type->getFrameRate());
    }

    private function assertDocumentMetadata(
        Metadata $result,
        int $pages = null,
        string $creator = null,
        string $title = null
    ): void {
        $type = $result->getType();
        self::assertInstanceOf(DocumentMetadata::class, $type);

        $this->getMetadataLoader()->updateMetadata($result);
        self::assertSame($pages, $type->getPages());
        self::assertSame($creator, $type->getCreator());
        self::assertSame($title, $type->getTitle());
    }

    private function assertNoMetadata(Metadata $result): void
    {
        $type = $result->getType();
        self::assertInstanceOf(NoMetadata::class, $type);

        $this->getMetadataLoader()->updateMetadata($result);
    }
}