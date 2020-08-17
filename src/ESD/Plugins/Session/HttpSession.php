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
        $plugin = Server::$instance->getPlugManager()->getPlug(SessionPlugin::class);
        if ($plugin instanceof SessionPlugin) {
            $this->sessionStorage = $plugin->getSessionStorage();
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
        \Swoole\Coroutine::defer(function () {
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

    /**
     * @var string the name of the session variable that stores the flash message data.
     */
    public $flashParam = '__flash';

    /**
     * Returns a flash message.
     * @param string $key the key identifying the flash message
     * @param mixed $defaultValue value to be returned if the flash message does not exist.
     * @param bool $delete whether to delete this flash message right after this method is called.
     * If false, the flash message will be automatically deleted in the next request.
     * @return mixed the flash message or an array of messages if addFlash was used
     * @see setFlash()
     * @see addFlash()
     * @see hasFlash()
     * @see getAllFlashes()
     * @see removeFlash()
     */
    public function getFlash($key, $defaultValue = null, $delete = false)
    {
        $counters = $this->getAttribute($this->flashParam, []);
        if (isset($counters[$key])) {
            $value = $this->getAttribute($key, $defaultValue);
            if ($delete) {
                $this->removeFlash($key);
            } elseif ($counters[$key] < 0) {
                // mark for deletion in the next request
                $counters[$key] = 1;
                $this->setAttribute($this->flashParam, $counters);
            }

            return $value;
        }

        return $defaultValue;
    }

    /**
     * Returns all flash messages.
     *
     * You may use this method to display all the flash messages in a view file:
     *
     * ```php
     * <?php
     * foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
     *     echo '<div class="alert alert-' . $key . '">' . $message . '</div>';
     * } ?>
     * ```
     *
     * With the above code you can use the [bootstrap alert][] classes such as `success`, `info`, `danger`
     * as the flash message key to influence the color of the div.
     *
     * Note that if you use [[addFlash()]], `$message` will be an array, and you will have to adjust the above code.
     *
     * [bootstrap alert]: http://getbootstrap.com/components/#alerts
     *
     * @param bool $delete whether to delete the flash messages right after this method is called.
     * If false, the flash messages will be automatically deleted in the next request.
     * @return array flash messages (key => message or key => [message1, message2]).
     * @see setFlash()
     * @see addFlash()
     * @see getFlash()
     * @see hasFlash()
     * @see removeFlash()
     */
    public function getAllFlashes($delete = false)
    {
        $counters = $this->getAttribute($this->flashParam, []);
        $flashes = [];
        foreach (array_keys($counters) as $key) {
            if (array_key_exists($key, $_SESSION)) {
                $flashes[$key] = $this->getAttribute($key);
                if ($delete) {
                    unset($counters[$key]);
                    $this->removeAttribute($key);
                } elseif ($counters[$key] < 0) {
                    // mark for deletion in the next request
                    $counters[$key] = 1;
                }
            } else {
                unset($counters[$key]);
            }
        }

        $this->setAttribute($this->flashParam, $counters);

        return $flashes;
    }

    /**
     * Sets a flash message.
     * A flash message will be automatically deleted after it is accessed in a request and the deletion will happen
     * in the next request.
     * If there is already an existing flash message with the same key, it will be overwritten by the new one.
     * @param string $key the key identifying the flash message. Note that flash messages
     * and normal session variables share the same name space. If you have a normal
     * session variable using the same name, its value will be overwritten by this method.
     * @param mixed $value flash message
     * @param bool $removeAfterAccess whether the flash message should be automatically removed only if
     * it is accessed. If false, the flash message will be automatically removed after the next request,
     * regardless if it is accessed or not. If true (default value), the flash message will remain until after
     * it is accessed.
     * @see getFlash()
     * @see addFlash()
     * @see removeFlash()
     */
    public function setFlash($key, $value = true, $removeAfterAccess = true)
    {
        $counters = $this->getAttribute($this->flashParam, []);
        $counters[$key] = $removeAfterAccess ? -1 : 0;
        $this->setAttribute($key, $value);
        $this->setAttribute($this->flashParam, $counters);
    }

    /**
     * Adds a flash message.
     * If there are existing flash messages with the same key, the new one will be appended to the existing message array.
     * @param string $key the key identifying the flash message.
     * @param mixed $value flash message
     * @param bool $removeAfterAccess whether the flash message should be automatically removed only if
     * it is accessed. If false, the flash message will be automatically removed after the next request,
     * regardless if it is accessed or not. If true (default value), the flash message will remain until after
     * it is accessed.
     * @see getFlash()
     * @see setFlash()
     * @see removeFlash()
     */
    public function addFlash($key, $value = true, $removeAfterAccess = true)
    {
        $counters = $this->getAttribute($this->flashParam, []);
        $counters[$key] = $removeAfterAccess ? -1 : 0;
        $this->setAttribute($this->flashParam, $counters);

        $attribute = $this->getAttribute($key);
        if (empty($attribute[$key])) {
            $this->setAttribute($key, [$value]);
        } elseif (is_array($attribute[$key])) {
            $attribute[$key][] = $value;
            $this->setAttribute($key, $attribute);
        } else {
            $this->setAttribute($key, [$_SESSION[$key], $value]);
        }
    }

    /**
     * Removes a flash message.
     * @param string $key the key identifying the flash message. Note that flash messages
     * and normal session variables share the same name space.  If you have a normal
     * session variable using the same name, it will be removed by this method.
     * @return mixed the removed flash message. Null if the flash message does not exist.
     * @see getFlash()
     * @see setFlash()
     * @see addFlash()
     * @see removeAllFlashes()
     */
    public function removeFlash($key)
    {
        $counters = $this->getAttribute($this->flashParam, []);
        $attribute = $this->getAttribute($key);
        $value = isset($attribute, $counters[$key]) ? $attribute : null;
        unset($counters[$key]);
        $this->removeAttribute($key);
        $this->setAttribute($this->flashParam, $counters);

        return $value;
    }

    /**
     * Removes all flash messages.
     * Note that flash messages and normal session variables share the same name space.
     * If you have a normal session variable using the same name, it will be removed
     * by this method.
     * @see getFlash()
     * @see setFlash()
     * @see addFlash()
     * @see removeFlash()
     */
    public function removeAllFlashes()
    {
        $counters = $this->getAttribute($this->flashParam, []);
        foreach (array_keys($counters) as $key) {
            $this->removeAttribute($key);
        }
        $this->removeAttribute($this->flashParam);
    }

    /**
     * Returns a value indicating whether there are flash messages associated with the specified key.
     * @param string $key key identifying the flash message type
     * @return bool whether any flash messages exist under specified key
     */
    public function hasFlash($key)
    {
        return $this->getFlash($key) !== null;
    }
}
