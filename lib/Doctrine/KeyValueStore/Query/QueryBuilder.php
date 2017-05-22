<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\KeyValueStore\Query;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\KeyValueStore\EntityManager;

/**
 * Range Query Object. It always requires a partition/hash key and
 * optionally conditions on the range key.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class QueryBuilder
{
    /**
     * @param string
     */
    protected $className;

    /**
     * @var array
     */
    protected $condition = null;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Expr
     **/
    protected $expr;

    /**
     * The query parameters.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $parameters;

    public function __construct(EntityManager $em, $className)
    {
        $this->em           = $em;
        $this->className    = $className;
        $this->parameters   = new ArrayCollection();
    }

    /**
     * Get className.
     *
     * @return className.
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get condition
     *
     * @return array
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Execute query and return a result iterator.
     *
     * @return ResultIterator
     */
    public function getResult()
    {
        $storage = $this->em->unwrap();

        if (! $storage instanceof QueryBuilderStorage) {
            throw new \RuntimeException(
                'The storage backend ' . $storage->getName() . ' does not support query builder queries.'
            );
        }

        $uow   = $this->em->getUnitOfWork();
        $class = $this->em->getClassMetadata($this->className);

        $rowHydration = function ($row) use ($uow, $class) {
            $key = [];

            foreach ($class->identifier as $id) {
                //TODO check if row value is not set
                $key[$id] = $row[$id];
            }

            return $uow->createEntity($class, $key, $row);
        };

        return $storage->executeQueryBuilder($this, $class->storageName, $class->identifier, $rowHydration);
    }

    /**
     * Specifies one or more restrictions to the query result.
     * Replaces any previously specified restrictions, if any.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = ?');
     *
     *     // You can optionally programatically build and/or expressions
     *     $qb = $em->createQueryBuilder();
     *
     *     $or = $qb->expr()->orx();
     *     $or->add($qb->expr()->eq('u.id', 1));
     *     $or->add($qb->expr()->eq('u.id', 2));
     *
     *     $qb->update('User', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where($or);
     * </code>
     *
     * @param mixed $predicates The restriction predicates.
     *
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function where($predicates)
    {
        if ( ! (func_num_args() == 1 && $predicates instanceof Expr\Composite)) {
            $predicates = new Expr\Andx(func_get_args());
        }

        $this->condition = $predicates;
        return $this;
    }

    /**
     * Adds one or more restrictions to the query results, forming a logical
     * conjunction with any previously specified restrictions.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.username LIKE ?')
     *         ->andWhere('u.is_active = 1');
     * </code>
     *
     * @param mixed $where The query restrictions.
     *
     * @return QueryBuilder This QueryBuilder instance.
     *
     * @see where()
     */
    public function andWhere()
    {
        $args  = func_get_args();
        $where = $this->condition;

        if ($where instanceof Expr\Andx) {
            $where->addMultiple($args);
        } else {
            array_unshift($args, $where);
            $where = new Expr\Andx($args);
        }

        $this->condition = $where;
        return $this;
    }

    /**
     * Adds one or more restrictions to the query results, forming a logical
     * disjunction with any previously specified restrictions.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = 1')
     *         ->orWhere('u.id = 2');
     * </code>
     *
     * @param mixed $where The WHERE statement.
     *
     * @return QueryBuilder
     *
     * @see where()
     */
    public function orWhere()
    {
        $args  = func_get_args();
        $where = $this->condition;

        if ($where instanceof Expr\Orx) {
            $where->addMultiple($args);
        } else {
            array_unshift($args, $where);
            $where = new Expr\Orx($args);
        }

        $this->condition = $where;
        return $this;
    }

    /**
     *
     * @return Query\Expr
     */
    public function expr()
    {
        if ($this->expr === null) {
            $this->expr = new Expr;
        }

        return $this->expr;
    }

    /**
     * Sets a query parameter for the query being constructed.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = :user_id')
     *         ->setParameter('user_id', 1);
     * </code>
     *
     * @param string|integer $key   The parameter position or name.
     * @param mixed          $value The parameter value.
     * @param string|null    $type  PDO::PARAM_* or \Doctrine\DBAL\Types\Type::* constant
     *
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function setParameter($key, $value, $type = null)
    {
        $filteredParameters = $this->parameters->filter(
            function ($parameter) use ($key)
            {
                // Must not be identical because of string to integer conversion
                return ($key == $parameter->getName());
            }
        );

        if (count($filteredParameters)) {
            $parameter = $filteredParameters->first();
            $parameter->setValue($value, $type);

            return $this;
        }

        $parameter = new Parameter($key, $value, $type);

        $this->parameters->add($parameter);

        return $this;
    }

    /**
     * Gets all defined query parameters for the query being constructed.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The currently defined query parameters.
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
