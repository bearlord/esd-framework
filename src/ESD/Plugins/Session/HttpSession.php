<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Session;

use ESD\Core\Server\Beans\Http\Cookie;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Server;

class HttpSession
{
    /**
     * @var bool
     */
    protected $isNew;

    /**
     * @var array
     */
    protected $attribute;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var SessionStorage
     */
    protected $sessionStorage;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;


    /**
     * @var SessionConfig
     */
    protected $config;

    /**
     * HttpSession constructor.
     */
    public function __construct()
    {
        $plug = Server::$instance->getPlugManager()->getPlug(SessionPlugin::class);
        if ($plug instanceof SessionPlugin) {
            $this->sessionStorage = $plug->getSessionStorage();
        }
        $this->config = DIGet(SessionConfig::class);
        setContextValue("HttpSession", $this);
        $this->request = getDeepContextValueByClassName(Request::class);
        $this->response = getDeepContextValueByClassName(Response::class);
        if ($this->config->getSessionUsage() == SessionConfig::USAGE_COOKIE) {
            $this->id = $this->request->getCookieParams()[$this->config->getSessionName()] ?? null;
        } elseif ($this->config->getSessionUsage() == SessionConfig::USEAGE_HEADER) {
            /** @var array $_sesionIdentify */
            $_sesionIdentify = $this->request->getHeader(SessionConfig::HEADER_IDENTIFY);
            $this->id = !empty($_sesionIdentify[0]) ? $_sesionIdentify[0] : null;
        } else {
            $authorization = explode(' ', $this->request->getHeaderLine('authorization'));
            if (isset($authorization[1])) {
                $this->id = $authorization[1];
            }
        }
        if ($this->id != null) {
            $this->isNew = false;
            $result = $this->sessionStorage->get($this->id);
            if ($result != null) {
                $this->attribute = serverUnSerialize($result);
            } else {
                $this->attribute = [];
            }
        }
        defer(function () {
            $this->save();
        });
    }

    /**
     * Create
     */
    public function create(): void
    {
        $this->refresh();
    }

    /**
     * Is available
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isExist() && !$this->isOverdue();
    }

    /**
     * Is overdue
     * @return bool
     */
    public function isOverdue(): bool
    {
        if (empty($this->attribute)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is exist
     * @return bool
     */
    public function isExist(): bool
    {
        if ($this->id != null) return true;
        return false;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Set attribute
     * @param string $key
     * @param string $value
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attribute[$key] = $value;
    }

    /**
     * Remove attribute
     * @param string $key
     */
    public function removeAttribute(string $key): void
    {
        unset($this->attribute[$key]);
    }

    /**
     * Refresh
     */
    public function refresh(): void
    {
        $id = $this->getId();
        if ($id != null) {
            $this->sessionStorage->remove($id);
        }
        $this->id = $this->gid();
        if ($this->config->getSessionUsage() == SessionConfig::USAGE_COOKIE) {
            $this->response->withCookie(new Cookie($this->config->getSessionName(), $this->id,
                time() + $this->config->getTimeout(), $this->config->getPath(),
                $this->config->getDomain(), $this->config->getSecure(), $this->config->getHttpOnly()));
        } elseif ($this->config->getSessionUsage() == SessionConfig::USEAGE_HEADER) {
            /** @var array $_sesionIdentify */
            $_sesionIdentify = $this->request->getHeader(SessionConfig::HEADER_IDENTIFY);
            if (!empty($_sesionIdentify[0])) {
                $sesionIdentify = $_sesionIdentify[0];
                $this->response->withHeader(SessionConfig::HEADER_IDENTIFY, $sesionIdentify);
            }
        } else {
            $this->response->withHeader('Authorization', 'Bearer ' . $this->id);
        }
        $this->setAttribute("createTime", time());
        $this->setAttribute("expireTime", time() + $this->config->getTimeout());

        $this->isNew = true;
    }

    /**
     * @return int
     */
    public function getExpireTime(): int  {
        return $this->getAttribute('expireTime');
    }


    /**
     * Get attriubute
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key = null)
    {
        if ($key == null) {
            return $this->attribute;
        }
        return $this->attribute[$key] ?? null;
    }


    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Invalidate
     */
    public function invalidate()
    {
        if ($this->id != null) {
            $this->sessionStorage->remove($this->id);
            $this->response->withCookie(new Cookie($this->config->getSessionName(), null, time() - 1));
        }
        $this->id = null;
        $this->attribute = [];
    }

    /**
     * Destroy
     */
    public function destroy()
    {
        $this->invalidate();
    }

    /**
     * Save
     */
    private function save()
    {
        if (!empty($this->attribute) && $this->id != null) {
            $this->sessionStorage->set($this->id, serverSerialize($this->attribute));
        }
    }

    /**
     * Generate id
     * @return string
     */
    private function gid()
    {
        return session_create_id();
    }
}
