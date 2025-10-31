<?php

declare(strict_types=1);

namespace Tourze\FaceDetectBundle\Contract;

/**
 * 百度AI客户端接口
 *
 * 封装百度人脸识别API调用，提供统一的接口抽象
 */
interface BaiduAiClientInterface
{
    /**
     * 人脸检测
     *
     * @param string               $imageData 图片数据（base64编码）
     * @param string               $imageType 图片类型（BASE64、URL、FACE_TOKEN）
     * @param array<string, mixed> $options   检测选项
     *
     * @return array<string, mixed> 检测结果
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function detectFace(string $imageData, string $imageType = 'BASE64', array $options = []): array;

    /**
     * 人脸对比
     *
     * @param array<int, array<string, mixed>> $faceList 人脸列表，每个元素包含image、image_type、face_type等
     * @param array<string, mixed>             $options  对比选项
     *
     * @return array<string, mixed> 对比结果
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function compareFaces(array $faceList, array $options = []): array;

    /**
     * 人脸搜索
     *
     * @param string               $imageData   图片数据
     * @param string               $imageType   图片类型
     * @param string               $groupIdList 用户组ID列表
     * @param array<string, mixed> $options     搜索选项
     *
     * @return array<string, mixed> 搜索结果
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function searchFace(
        string $imageData,
        string $imageType,
        string $groupIdList,
        array $options = [],
    ): array;

    /**
     * 人脸注册
     *
     * @param string               $imageData 图片数据
     * @param string               $imageType 图片类型
     * @param string               $groupId   用户组ID
     * @param string               $userId    用户ID
     * @param array<string, mixed> $options   注册选项
     *
     * @return array<string, mixed> 注册结果
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function addFace(
        string $imageData,
        string $imageType,
        string $groupId,
        string $userId,
        array $options = [],
    ): array;

    /**
     * 人脸更新
     *
     * @param string               $imageData 图片数据
     * @param string               $imageType 图片类型
     * @param string               $groupId   用户组ID
     * @param string               $userId    用户ID
     * @param array<string, mixed> $options   更新选项
     *
     * @return array<string, mixed> 更新结果
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function updateFace(
        string $imageData,
        string $imageType,
        string $groupId,
        string $userId,
        array $options = [],
    ): array;

    /**
     * 人脸删除
     *
     * @param string $userId    用户ID
     * @param string $groupId   用户组ID
     * @param string $faceToken 人脸token
     *
     * @return array<string, mixed> 删除结果
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function deleteFace(string $userId, string $groupId, string $faceToken): array;

    /**
     * 获取用户人脸列表
     *
     * @param string $userId  用户ID
     * @param string $groupId 用户组ID
     *
     * @return array<string, mixed> 人脸列表
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function getFaceList(string $userId, string $groupId): array;

    /**
     * 获取用户组列表
     *
     * @param int $start  起始位置
     * @param int $length 返回数量
     *
     * @return array<string, mixed> 用户组列表
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function getGroupList(int $start = 0, int $length = 100): array;

    /**
     * 获取用户列表
     *
     * @param string $groupId 用户组ID
     * @param int    $start   起始位置
     * @param int    $length  返回数量
     *
     * @return array<string, mixed> 用户列表
     *
     * @throws \Exception 当API调用失败时抛出异常
     */
    public function getUserList(string $groupId, int $start = 0, int $length = 100): array;

    /**
     * 获取Access Token
     *
     * @return string Access Token
     *
     * @throws \Exception 当获取Token失败时抛出异常
     */
    public function getAccessToken(): string;

    /**
     * 检查API配置是否有效
     *
     * @return bool 配置是否有效
     */
    public function isConfigValid(): bool;
}
