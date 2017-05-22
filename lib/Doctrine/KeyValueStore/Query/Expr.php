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

/**
 * This class is used to generate query expressions via a set of PHP static functions.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @todo Rename: ExpressionBuilder
 */
class Expr
{
    /**
     * Creates a conjunction of the given boolean expressions.
     *
     * Example:
     *
     *     [php]
     *     // (u.type = ?1) AND (u.role = ?2)
     *     $expr->andX($expr->eq('u.type', ':1'), $expr->eq('u.role', ':2'));
     *
     * @param \Doctrine\KeyValueStore\Query\Expr\Comparison |
     *        \Doctrine\KeyValueStore\Query\Expr\Func |
     *        \Doctrine\KeyValueStore\Query\Expr\Orx
     *        $x Optional clause. Defaults to null, but requires at least one defined when converting to string.
     *
     * @return Expr\Andx
     */
    public function andX($x = null)
    {
        return new Expr\Andx(func_get_args());
    }

    /**
     * Creates a disjunction of the given boolean expressions.
     *
     * Example:
     *
     *     [php]
     *     // (u.type = ?1) OR (u.role = ?2)
     *     $q->where($q->expr()->orX('u.type = ?1', 'u.role = ?2'));
     *
     * @param mixed $x Optional clause. Defaults to null, but requires
     *                 at least one defined when converting to string.
     *
     * @return Expr\Orx
     */
    public function orX($x = null)
    {
        return new Expr\Orx(func_get_args());
    }

    /**
     * Creates an equality comparison expression with the given arguments.
     *
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> = <right expr>. Example:
     *
     *     [php]
     *     // u.id = ?1
     *     $expr->eq('u.id', '?1');
     *
     * @param mixed $x Left expression.
     * @param mixed $y Right expression.
     *
     * @return Expr\Comparison
     */
    public function eq($x, $y)
    {
        return new Expr\Comparison($x, Expr\Comparison::EQ, $y);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <> <right expr>. Example:
     *
     *     [php]
     *     // u.id <> ?1
     *     $q->where($q->expr()->neq('u.id', '?1'));
     *
     * @param mixed $x Left expression.
     * @param mixed $y Right expression.
     *
     * @return Expr\Comparison
     */
    public function neq($x, $y)
    {
        return new Expr\Comparison($x, Expr\Comparison::NEQ, $y);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> < <right expr>. Example:
     *
     *     [php]
     *     // u.id < ?1
     *     $q->where($q->expr()->lt('u.id', '?1'));
     *
     * @param mixed $x Left expression.
     * @param mixed $y Right expression.
     *
     * @return Expr\Comparison
     */
    public function lt($x, $y)
    {
        return new Expr\Comparison($x, Expr\Comparison::LT, $y);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <= <right expr>. Example:
     *
     *     [php]
     *     // u.id <= ?1
     *     $q->where($q->expr()->lte('u.id', '?1'));
     *
     * @param mixed $x Left expression.
     * @param mixed $y Right expression.
     *
     * @return Expr\Comparison
     */
    public function lte($x, $y)
    {
        return new Expr\Comparison($x, Expr\Comparison::LTE, $y);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> > <right expr>. Example:
     *
     *     [php]
     *     // u.id > ?1
     *     $q->where($q->expr()->gt('u.id', '?1'));
     *
     * @param mixed $x Left expression.
     * @param mixed $y Right expression.
     *
     * @return Expr\Comparison
     */
    public function gt($x, $y)
    {
        return new Expr\Comparison($x, Expr\Comparison::GT, $y);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> >= <right expr>. Example:
     *
     *     [php]
     *     // u.id >= ?1
     *     $q->where($q->expr()->gte('u.id', '?1'));
     *
     * @param mixed $x Left expression.
     * @param mixed $y Right expression.
     *
     * @return Expr\Comparison
     */
    public function gte($x, $y)
    {
        return new Expr\Comparison($x, Expr\Comparison::GTE, $y);
    }

    /**
     * Creates an IN() expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by IN() function.
     * @param mixed  $y Argument to be used in IN() function.
     *
     * @return Expr\Func
     */
    public function in($x, $y)
    {
        if (is_array($y)) {
            foreach ($y as &$literal) {
                if ( ! ($literal instanceof Expr\Literal)) {
                    $literal = $this->_quoteLiteral($literal);
                }
            }
        }
        return new Expr\Func($x . ' IN', (array) $y);
    }

    /**
     * Creates a NOT IN() expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by NOT IN() function.
     * @param mixed $y Argument to be used in NOT IN() function.
     *
     * @return Expr\Func
     */
    public function notIn($x, $y)
    {
        if (is_array($y)) {
            foreach ($y as &$literal) {
                if ( ! ($literal instanceof Expr\Literal)) {
                    $literal = $this->_quoteLiteral($literal);
                }
            }
        }
        return new Expr\Func($x . ' NOT IN', (array) $y);
    }

    /**
     * Creates a CONTAINS() comparison expression with the given arguments.
     *
     * @param string $x Field in string format to be inspected by CONTAINS() comparison.
     * @param mixed  $y Argument to be used in CONTAINS() comparison.
     *
     * @return Expr\Comparison
     */
    public function contains($x, $y)
    {
        return new Expr\Func('contains', [$x, $y]);
    }

    /**
     * Creates a NOT CONTAINS() comparison expression with the given arguments.
     *
     * @param string $x Field in string format to be inspected by LIKE() comparison.
     * @param mixed  $y Argument to be used in LIKE() comparison.
     *
     * @return Expr\Comparison
     */
    public function notLike($x, $y)
    {
        return new Expr\Comparison($x, 'NOT CONTAINS', $y);
    }

    /**
     * Creates a SIZE() function expression with the given argument.
     *
     * @param mixed $x Argument to be used as argument of SIZE() function.
     *
     * @return Expr\Func A SIZE function expression.
     */
    public function size($x)
    {
        return new Expr\Func('SIZE', array($x));
    }

    /**
     * Creates an instance of BETWEEN() function, with the given argument.
     *
     * @param mixed   $val Valued to be inspected by range values.
     * @param integer $x   Starting range value to be used in BETWEEN() function.
     * @param integer $y   End point value to be used in BETWEEN() function.
     *
     * @return Expr\Func A BETWEEN expression.
     */
    public function between($val, $x, $y)
    {
        return $val . ' BETWEEN ' . $x . ' AND ' . $y;
    }
}
